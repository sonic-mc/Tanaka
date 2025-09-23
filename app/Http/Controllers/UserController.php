<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->orderBy('name')->paginate(20);
        return view('admin.users.manage', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'role' => 'required|in:admin,psychiatrist,nurse,clinician',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        User::create($validated);

        return redirect()->back()->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,psychiatrist,nurse,clinician',
        ]);

        $user->update($validated);
        return redirect()->back()->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->back()->with('success', 'User deleted.');
    }

    public function deactivate(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        $user->update(['active' => false]);

        return redirect()->back()->with('success', 'User deactivated.');
    }

    public function updateRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'new_role' => 'required|in:admin,psychiatrist,nurse,clinician',
        ]);

        $user = User::find($request->user_id);
        $user->update(['role' => $request->new_role]);

        return redirect()->back()->with('success', 'Role updated.');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'new_password' => 'required|string|min:8',
        ]);

        $user = User::find($request->user_id);
        $user->update(['password' => Hash::make($request->new_password)]);

        return redirect()->back()->with('success', 'Password reset successfully.');
    }

    public function auditLogs(Request $request)
    {
        $logs = UserLog::with('user')
        ->when($request->filled('user_id'), fn($q) => $q->where('user_id', $request->user_id))
        ->orderBy('created_at', 'desc')
        ->paginate(30);

    $users = User::orderBy('name')->get();
        return view('admin.users.manage', compact('logs', 'users'));
    }
}
