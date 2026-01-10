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
            'client' => $client,
        ]);
    }

    public function update(StoreClientRequest $request, Company $client): RedirectResponse
    {
        $client->update($request->validated());
        return redirect()->route('clients.index')->with('success', 'Cliente actualizado exitosamente.');
    }
}
