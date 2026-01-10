<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProjectMediaController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        // Check file limit (max 5 files per project)
        $currentFileCount = $project->getMedia('project_files')->count();
        if ($currentFileCount >= 5) {
            throw ValidationException::withMessages([
                'file' => 'Este proyecto ya tiene el máximo de 5 archivos permitidos.',
            ]);
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,zip', 'max:5120'], // 5MB max
        ], [
            'file.mimes' => 'Solo se permiten archivos PDF, Word, Excel o ZIP.',
            'file.max' => 'El archivo no puede superar 5MB.',
        ]);

        $project->addMediaFromRequest('file')
            ->toMediaCollection('project_files');

        return redirect()->back()->with('success', 'Archivo de proyecto subido.');
    }

    public function show(Project $project, string $mediaId)
    {
        $media = $project->media()->findOrFail($mediaId);

        // Authorization check logic here if needed (e.g. project view policy)
        // $this->authorize('view', $project);

        return response()->file($media->getPath(), [
            'Content-Type' => $media->mime_type,
            'Content-Disposition' => 'inline; filename="' . $media->file_name . '"',
        ]);
    }

    public function destroy(Project $project, string $mediaId): RedirectResponse
    {
        $media = $project->media()->findOrFail($mediaId);
        $media->delete();

        return redirect()->back()->with('success', 'Archivo eliminado.');
    }
}
