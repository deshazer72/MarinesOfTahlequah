<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of users with their roles
     */
    public function index()
    {
        $users = User::orderBy('name')
            ->paginate(15);

        return Inertia::render('Users/Index', [
            'users' => $users
        ]);
    }

    /**
     * Update the user's role
     */
    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:user,admin,superadmin'
        ]);

        // Prevent users from changing their own role
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot change your own role.');
        }

        // Prevent removal of the last super admin
        if ($user->role === 'superadmin' && $request->role !== 'superadmin') {
            $superAdminCount = User::where('role', 'superadmin')->count();
            if ($superAdminCount <= 1) {
                return back()->with('error', 'Cannot remove the last super admin.');
            }
        }

        $user->update(['role' => $request->role]);

        return back()->with('success', 'User role updated successfully.');
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        // Prevent super admin from deleting themselves
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot delete yourself.']);
        }

        // Prevent deleting the last super admin
        if ($user->role === 'superadmin' && User::where('role', 'superadmin')->count() <= 1) {
            return back()->withErrors(['error' => 'Cannot delete the last super admin.']);
        }

        $user->delete();

        return back()->with('success', 'User deleted successfully!');
    }
}
