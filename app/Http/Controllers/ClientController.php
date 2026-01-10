<?php

namespace App\Http\Controllers;

use App\Actions\Clients\CreateCompanyAction;
use App\Http\Requests\Clients\StoreClientRequest;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
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

    public function updateUser(\Illuminate\Http\Request $request, Company $client, \App\Models\User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        
        if (!empty($validated['password'])) {
            $user->password = \Illuminate\Support\Facades\Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->back()->with('success', 'Usuario actualizado exitosamente.');
    }
}
