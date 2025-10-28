<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use App\Traits\AuditLogger;

class UserController extends Controller
{
    use AuditLogger;

    /**
     * Listing + tabs entry point.
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'view-users');
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($q2) use ($q) {
                $q2->where('name', 'like', "%{$q}%")
                   ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $users = $query->orderBy('name')->paginate(20)->withQueryString();

        $logs = null;
        if ($tab === 'audit-logs') {
            $logsQuery = AuditLog::with('user')->orderByDesc('timestamp');
            if ($request->filled('user_id')) {
                $logsQuery->where('user_id', $request->user_id);
            }
            $logs = $logsQuery->paginate(30)->withQueryString();
        }

        return view('admin.users.manage', [
            'users' => $users,
            'logs' => $logs,
        ]);
    }

    /**
     * Create user (POST) – role optional
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => ['nullable', Rule::in(['admin', 'psychiatrist', 'nurse', 'clinician'])],
            'password' => 'required|string|min:8|confirmed',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'] ?? null, // allow NULL role
                'password' => Hash::make($validated['password']),
            ]);

            if (method_exists($this, 'audit')) {
                $this->audit('info', 'user-created', [
                    'module' => 'users',
                    'user_id' => $user->id,
                    'created_by' => Auth::id(),
                    'description' => "User {$user->email} created",
                ]);
            }

            DB::commit();

            return redirect()->route('admin.users.index', ['tab' => 'view-users'])
                ->with('success', 'User created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withInput()
                ->withErrors(['error' => 'Could not create user: ' . $e->getMessage()]);
        }
    }

    /**
     * Edit form
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update user – role optional
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required','email', Rule::unique('users','email')->ignore($user->id)],
            'role' => ['nullable', Rule::in(['admin', 'psychiatrist', 'nurse', 'clinician'])],
        ]);

        DB::beginTransaction();
        try {
            $oldRole = $user->role;
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'] ?? null,
            ]);

            if (method_exists($this, 'audit')) {
                $this->audit('info', 'user-updated', [
                    'module' => 'users',
                    'user_id' => $user->id,
                    'updated_by' => Auth::id(),
                    'old_role' => $oldRole,
                    'new_role' => $user->role,
                    'description' => "User {$user->email} updated",
                ]);
            }

            DB::commit();

            return redirect()->route('admin.users.index', ['tab' => 'view-users'])
                ->with('success', 'User updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withInput()->withErrors(['error' => 'Could not update user: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete user (hard delete)
     */
    public function destroy(User $user)
    {
        if (Auth::id() === $user->id) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        DB::beginTransaction();
        try {
            $user->delete();

            if (method_exists($this, 'audit')) {
                $this->audit('warning', 'user-deleted', [
                    'module' => 'users',
                    'user_id' => $user->id,
                    'deleted_by' => Auth::id(),
                    'description' => "User {$user->email} deleted",
                ]);
            }

            DB::commit();

            return redirect()->route('admin.users.index', ['tab' => 'view-users'])
                ->with('success', 'User deleted.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors(['error' => 'Could not delete user: ' . $e->getMessage()]);
        }
    }

    /**
     * Update role separately (admin assigns role)
     */
    public function updateRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'new_role' => ['required', Rule::in(['admin', 'psychiatrist', 'nurse', 'clinician'])],
        ]);

        $user = User::findOrFail($request->user_id);

        DB::beginTransaction();
        try {
            $oldRole = $user->role;
            $user->update(['role' => $request->new_role]);

            if (method_exists($this, 'audit')) {
                $this->audit('info', 'user-role-updated', [
                    'module' => 'users',
                    'user_id' => $user->id,
                    'old_role' => $oldRole,
                    'new_role' => $request->new_role,
                    'updated_by' => Auth::id(),
                    'description' => "Role changed from {$oldRole} to {$request->new_role}",
                ]);
            }

            DB::commit();

            return back()->with('success', 'Role updated.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors(['error' => 'Could not update role: ' . $e->getMessage()]);
        }
    }

    /**
     * Dashboard routing based on role
     */
    public function dashboard()
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (empty($user->role)) {
            return $this->defaultDashboard();
        }

        return match ($user->role) {
            'admin'        => $this->adminDashboard(),
            'psychiatrist' => $this->psychiatristDashboard(request()),
            'nurse'        => $this->nurseDashboard(),
            'clinician'    => $this->clinicianDashboard(),
            default        => $this->defaultDashboard(),
        };
    }
}
