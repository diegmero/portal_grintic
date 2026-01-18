<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class AdminUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get users with admin role
        $admins = User::role('admin')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Admins/Index', [
            'admins' => $admins,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admins/Edit', [ // Reusing Edit for Create if possible, or Create view
            'admin' => null, // null indicates creation mode
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => true,
        ]);

        // Key Step: Assign Admin Role
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $user->assignRole($adminRole);

        return redirect()->route('admins.index')->with('success', 'Administrador creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $admin)
    {
        // Verify it is an admin
        if (!$admin->hasRole('admin')) {
             return redirect()->route('admins.index')->withErrors(['error' => 'Usuario no es administrador.']);
        }

        return Inertia::render('Admins/Edit', [
            'admin' => $admin,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $admin)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $admin->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $admin->name = $request->name;
        $admin->email = $request->email;

        if ($request->filled('password')) {
            $admin->password = Hash::make($request->password);
        }

        $admin->save();

        return redirect()->route('admins.index')->with('success', 'Administrador actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $admin)
    {
        // 1. Prevent Self-Deletion
        if ($admin->id === Auth::id()) {
            return redirect()->back()->withErrors(['error' => 'No puedes eliminar tu propia cuenta mientras estás conectado.']);
        }

        // 2. Prevent deleting the last admin (optional but good resilience)
        $adminCount = User::role('admin')->count();
        if ($adminCount <= 1) {
             return redirect()->back()->withErrors(['error' => 'No puedes eliminar al último administrador.']);
        }

        $admin->delete(); // Soft delete as per model trait

        return redirect()->route('admins.index')->with('success', 'Administrador eliminado correctamente.');
    }
}
