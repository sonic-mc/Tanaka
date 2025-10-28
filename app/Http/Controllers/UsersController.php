<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UsersController extends Controller
{
    /**
     * Display a list of users for role management.
     * Only accessible by admin users.
     */
    public function index()
    {
        // Restrict to admins only
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access');
        }

        // Get users with no role explicitly set (null or empty)
        $users = User::whereNull('role')->orWhere('role', '')->get();

        // Count users without roles
        $noRoleCount = $users->count();

        // Define available roles manually
        $roles = ['admin', 'psychiatrist', 'nurse', 'clinician'];

        return view('users.index', compact('users', 'noRoleCount', 'roles'));
    }

    /**
     * Assign a role to a user.
     */
    public function assignRole(Request $request, $id)
    {
        // Restrict to admins only
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access');
        }

        $request->validate([
            'role' => 'required|in:admin,psychiatrist,nurse,clinician',
        ]);

        $user = User::findOrFail($id);
        $user->role = $request->role;
        $user->save();

        return redirect()->back()->with('success', "Role '{$request->role}' assigned to {$user->name} successfully!");
    }
}
