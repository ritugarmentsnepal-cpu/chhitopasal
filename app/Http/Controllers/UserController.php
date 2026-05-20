<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(20);
        $permissionKeys = User::PERMISSIONS;
        $permissionGroups = User::PERMISSION_GROUPS;
        $rolePresets = User::ROLE_PRESETS;
        return view('users.index', compact('users', 'permissionKeys', 'permissionGroups', 'rolePresets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'max:50'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ]);

        $role = $request->role;
        $permissions = $request->permissions;

        // If no explicit permissions provided, use role preset defaults
        if (empty($permissions)) {
            $permissions = User::getDefaultPermissions($role);
        }

        // Validate permission keys
        $validKeys = array_keys(User::PERMISSIONS);
        $permissions = array_values(array_intersect($permissions, $validKeys));

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
            'permissions' => $permissions,
        ]);

        return redirect()->route('users.index')->with('success', 'Staff account created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'max:50'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ]);

        $role = $request->role;
        $permissions = $request->permissions ?? [];

        // Validate permission keys
        $validKeys = array_keys(User::PERMISSIONS);
        $permissions = array_values(array_intersect($permissions, $validKeys));

        // Admin always gets all permissions
        if ($role === 'admin') {
            $permissions = $validKeys;
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $role,
            'permissions' => $permissions,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Staff account updated successfully.');
    }

    /**
     * AJAX endpoint for toggling individual permissions.
     */
    public function updatePermissions(Request $request, User $user)
    {
        $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string'],
        ]);

        // Don't allow editing admin permissions via this endpoint
        if ($user->role === 'admin') {
            return response()->json(['message' => 'Admin users always have full permissions.'], 422);
        }

        // Prevent editing your own permissions
        if (auth()->id() === $user->id) {
            return response()->json(['message' => 'You cannot edit your own permissions.'], 422);
        }

        $validKeys = array_keys(User::PERMISSIONS);
        $permissions = array_values(array_intersect($request->permissions, $validKeys));

        $user->update(['permissions' => $permissions]);

        return response()->json([
            'success' => true,
            'message' => 'Permissions updated successfully.',
            'permissions' => $permissions,
        ]);
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return redirect()->route('users.index')->with('error', 'You cannot delete your own account.');
        }

        // Prevent deleting the last admin
        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return redirect()->route('users.index')->with('error', 'Cannot delete the last admin account.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Staff account deleted successfully.');
    }
}
