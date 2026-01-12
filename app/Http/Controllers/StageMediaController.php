<?php

namespace App\Http\Controllers;

use App\Models\Stage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StageMediaController extends Controller
{
    public function store(Request $request, Stage $stage): RedirectResponse
    {
        // Permission Check for Clients
        if ($request->user()->company_id && !$request->user()->can('upload_files')) {
            abort(403, 'No tienes permiso para subir archivos.');
        }

        // Check file limit (max 5 files per stage)
        $currentFileCount = $stage->getMedia('stage_files')->count();
        if ($currentFileCount >= 5) {
            throw ValidationException::withMessages([
                'file' => 'Esta etapa ya tiene el máximo de 5 archivos permitidos.',
            ]);
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf', 'max:5120'], // PDF only, 5MB max
        ], [
            'file.mimes' => 'Solo se permiten archivos PDF.',
            'file.max' => 'El archivo no puede superar 5MB.',
        ]);

        $stage->addMediaFromRequest('file')
            ->toMediaCollection('stage_files');

        return redirect()->back()->with('success', 'Archivo subido.');
    }

    public function destroy(Stage $stage, string $mediaId): RedirectResponse
    {
        if (request()->user()->company_id) {
            abort(403, 'Acción no permitida.');
        }

        $media = $stage->media()->findOrFail($mediaId);
        $media->delete();

        return redirect()->back()->with('success', 'Archivo eliminado.');
    }

    public function show(Stage $stage, string $mediaId)
    {
        $media = $stage->media()->findOrFail($mediaId);

        // Ensure user has access to the project
        // $this->authorize('view', $stage->project); // Policy check if needed

        return response()->file($media->getPath(), [
            'Content-Type' => $media->mime_type,
            'Content-Disposition' => 'inline; filename="' . $media->file_name . '"',
        ]);
    }
}
