<?php

namespace App\Http\Controllers;

use App\Actions\Clients\CreateCompanyAction;
use App\Http\Requests\Clients\StoreClientRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Clients/Index', [
            'clients' => Company::withCount('projects')->latest()->get(),
        ]);
    }

    public function store(StoreClientRequest $request, CreateCompanyAction $action): RedirectResponse
    {
        $action->execute($request->validated());

        return redirect()->back()->with('success', 'Cliente creado exitosamente.');
    }

    public function edit(Company $client): Response
    {
        $stats = [
            'total_invoiced' => $client->invoices()->sum('total'),
            'pending_balance' => $client->invoices()->sum('balance_due'),
            'active_projects' => $client->projects()->where('status', 'active')->count(),
            'total_projects' => $client->projects()->count(),
            'total_contacts' => $client->users()->count(),
        ];

        return Inertia::render('Clients/Edit', [
            'client' => $client->load('users.permissions'),
            'projects' => $client->projects()->latest()->get(),
            'stats' => $stats,
        ]);
    }

    public function update(StoreClientRequest $request, Company $client): RedirectResponse
    {
        $client->update($request->validated());
        return redirect()->back()->with('success', 'Cliente actualizado exitosamente.');
    }

    public function updateUser(\Illuminate\Http\Request $request, Company $client, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'is_active' => 'boolean',
            'is_primary_contact' => 'boolean',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->is_active = $validated['is_active'] ?? true;
        
        // Primary Contact Logic
        if (isset($validated['is_primary_contact'])) {
             // If setting to true, disable others
            if ($validated['is_primary_contact']) {
                $client->users()->where('id', '!=', $user->id)->update(['is_primary_contact' => false]);
                $user->is_primary_contact = true;
            } else {
                $user->is_primary_contact = false;
            }
        }

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        if ($request->has('permissions')) {
            $user->syncPermissions($request->permissions);
        }

        return redirect()->back()->with('success', 'Usuario actualizado exitosamente.');
    }

    public function storeUser(\Illuminate\Http\Request $request, Company $client): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'is_active' => 'boolean',
            'is_primary_contact' => 'boolean',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        // If this is the first user or marked primary, handle logic
        $isPrimary = $validated['is_primary_contact'] ?? false;
        if ($client->users()->count() === 0) {
            $isPrimary = true;
        }

        if ($isPrimary) {
             $client->users()->update(['is_primary_contact' => false]);
        }

        $user = User::create([
            'company_id' => $client->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_active' => $validated['is_active'] ?? true,
            'is_primary_contact' => $isPrimary,
        ]);

        $user->assignRole('client');

        if ($request->has('permissions')) {
             $user->syncPermissions($request->permissions);
        }

        return redirect()->back()->with('success', 'Usuario creado exitosamente.');
    }

    public function destroyUser(Company $client, User $user): RedirectResponse
    {
        if ($user->company_id !== $client->id) {
            abort(403, 'El usuario no pertenece a este cliente.');
        }

        $user->delete();

        return redirect()->back()->with('success', 'Usuario eliminado exitosamente.');
    }
}
