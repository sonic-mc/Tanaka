<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UsersController extends Controller
{
    /**
     * Display a list of users for role management.
     * Only accessible by admin users.
     */
    public function index()
    {
        // Restrict to admins only
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized access');
        }
    
        // Get users with no roles
        $users = User::doesntHave('roles')->get();
    
        // Count users without roles
        $noRoleCount = $users->count();

         // Get all available roles
        $roles = Role::all();
    
        return view('users.index', compact('users', 'noRoleCount', 'roles'));
    }
    

    /**
     * Assign a role to a user.
     */
    public function assignRole(Request $request, $id)
    {
        // Restrict to admins only
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized access');
        }

        $request->validate([
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::findOrFail($id);

        // Remove any old roles and assign the new one
        $user->syncRoles([$request->role]);

        return redirect()->back()->with('success', "Role '{$request->role}' assigned to {$user->name} successfully!");
    }
}
