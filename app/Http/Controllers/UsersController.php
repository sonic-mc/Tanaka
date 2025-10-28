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
        $currentUser = auth()->user();

        // Restrict to admins only
        if (! $currentUser || $currentUser->role !== 'admin') {
            abort(403, 'Unauthorized access');
        }

        // Get users with no role explicitly set
        $usersWithoutRoles = User::whereNull('role')->orWhere('role', '')->get();
        $noRoleCount = $usersWithoutRoles->count();

        // Define available roles manually
        $availableRoles = ['admin', 'psychiatrist', 'nurse', 'clinician'];

        return view('users.index', [
            'users' => $usersWithoutRoles,
            'noRoleCount' => $noRoleCount,
            'roles' => $availableRoles,
        ]);
    }

    /**
     * Assign a role to a user.
     */
    public function assignRole(Request $request, $id)
    {
        $currentUser = auth()->user();

        // Restrict to admins only
        if (! $currentUser || $currentUser->role !== 'admin') {
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
