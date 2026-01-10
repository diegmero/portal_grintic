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
        // Check file limit (max 5 files per stage)
        $currentFileCount = $stage->getMedia('stage_files')->count();
        if ($currentFileCount >= 5) {
            throw ValidationException::withMessages([
                'file' => 'Esta etapa ya tiene el máximo de 5 archivos permitidos.',
            ]);
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf', 'max:10240'], // PDF only, 10MB max
        ], [
            'file.mimes' => 'Solo se permiten archivos PDF.',
            'file.max' => 'El archivo no puede superar 10MB.',
        ]);

        $stage->addMediaFromRequest('file')
            ->toMediaCollection('stage_files');

        return redirect()->back()->with('success', 'Archivo subido.');
    }

    public function destroy(Stage $stage, string $mediaId): RedirectResponse
    {
        $media = $stage->media()->findOrFail($mediaId);
        $media->delete();

        return redirect()->back()->with('success', 'Archivo eliminado.');
    }
}
