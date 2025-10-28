<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Traits\AuditLogger;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use AuditLogger;

    /**
     * Listing + tabs entry point.
     * Supports tab switching via ?tab=...
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'view-users');
  

        // Basic users query for several tabs (filters applied from request)
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
       

        // If the audit-logs tab is active, fetch audit logs (with optional user filter).
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
     * Create user (POST).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => ['required', Rule::in(['admin', 'psychiatrist', 'nurse', 'clinician'])],
            'password' => 'required|string|min:8|confirmed',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'],
                'password' => Hash::make($validated['password']),
            ]);

            // audit if trait available
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
     * Update user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required','email', Rule::unique('users','email')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'psychiatrist', 'nurse', 'clinician'])],
        ]);

        DB::beginTransaction();
        try {
            $user->update($validated);

            if (method_exists($this, 'audit')) {
                $this->audit('info', 'user-updated', [
                    'module' => 'users',
                    'user_id' => $user->id,
                    'updated_by' => Auth::id(),
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
     * Delete user (hard delete). Keep robust: prevent deleting currently authenticated user.
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
     * Deactivate user.
     * Note: the migration you provided does not include an `active` column.
     * This method will only work if a boolean `active` column exists. If it doesn't,
     * it returns a helpful error message.
     */
    public function deactivate(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        $user = User::findOrFail($request->user_id);

        if (! Schema::hasColumn('users', 'active')) {
            return back()->withErrors([
                'deactivate' => 'The users table does not have an "active" column. To enable deactivation, add a boolean `active` column to your users migration.'
            ]);
        }

        $user->update(['active' => false]);

        if (method_exists($this, 'audit')) {
            $this->audit('info', 'user-deactivated', [
                'module' => 'users',
                'user_id' => $user->id,
                'deactivated_by' => Auth::id(),
                'description' => "User {$user->email} deactivated",
            ]);
        }

        return redirect()->route('admin.users.index', ['tab' => 'manage-users'])
            ->with('success', 'User deactivated.');
    }

    /**
     * Update role (from manage UI)
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
     * Reset password (admin action). Does not send email automatically.
     * If you want an email, integrate Notification/Password broker.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::findOrFail($request->user_id);

        DB::beginTransaction();
        try {
            $user->update(['password' => Hash::make($request->new_password)]);

            if (method_exists($this, 'audit')) {
                $this->audit('info', 'user-password-reset', [
                    'module' => 'users',
                    'user_id' => $user->id,
                    'reset_by' => Auth::id(),
                    'description' => "Password reset by admin",
                ]);
            }

            DB::commit();

            return back()->with('success', 'Password reset successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors(['error' => 'Could not reset password: ' . $e->getMessage()]);
        }
    }

    /**
     * Audit logs viewer (separate action if you want to link directly)
     */
    public function auditLogs(Request $request)
    {
        $logsQuery = AuditLog::with('user')->orderByDesc('timestamp');

        if ($request->filled('user_id')) {
            $logsQuery->where('user_id', $request->user_id);
        }

        $logs = $logsQuery->paginate(30)->withQueryString();
        $users = User::orderBy('name')->get();

        return view('admin.users.manage', compact('logs', 'users'));
    }
}
