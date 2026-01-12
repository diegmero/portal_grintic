<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskMediaController extends Controller
{
    public function store(Request $request, Task $task)
    {
        // Permission Check for Clients
        if ($request->user()->company_id && !$request->user()->can('upload_files')) {
            abort(403, 'No tienes permiso para subir archivos.');
        }

        $request->validate([
            'headers' => 'required|file|max:10240', // 10MB max
        ]);

        $task->addMediaFromRequest('file')->toMediaCollection('default');

        if ($request->user()->hasRole('client')) {
            event(new \App\Events\ClientActivityDetected(
                "Cliente subió un archivo a la tarea: {$task->name}",
                $request->user()
            ));
        }

        return back()->with('success', 'Archivo subido correctamente.');
    }
}
