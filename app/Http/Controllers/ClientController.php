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
        return Inertia::render('Clients/Edit', [
            'client' => $client->load('users'),
        ]);
    }

    public function update(StoreClientRequest $request, Company $client): RedirectResponse
    {
        $client->update($request->validated());
        return redirect()->route('clients.index')->with('success', 'Cliente actualizado exitosamente.');
    }

    public function updateUser(\Illuminate\Http\Request $request, Company $client, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->back()->with('success', 'Usuario actualizado exitosamente.');
    }

    public function storeUser(\Illuminate\Http\Request $request, Company $client): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'company_id' => $client->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole('client');

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
