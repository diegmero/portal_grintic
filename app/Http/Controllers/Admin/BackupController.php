<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class BackupController extends Controller
{
    /**
     * Display list of backups from R2.
     */
    public function index()
    {
        $disk = Storage::disk('r2');
        $appName = str_replace(' ', '-', config('app.name'));
        $backupPath = $appName;

        $files = [];
        
        try {
            if ($disk->exists($backupPath)) {
                $allFiles = $disk->files($backupPath);
                
                foreach ($allFiles as $file) {
                    if (str_ends_with($file, '.zip')) {
                        $files[] = [
                            'name' => basename($file),
                            'path' => $file,
                            'size' => $this->formatBytes($disk->size($file)),
                            'size_bytes' => $disk->size($file),
                            'last_modified' => $disk->lastModified($file),
                            'date' => date('Y-m-d H:i:s', $disk->lastModified($file)),
                        ];
                    }
                }
                
                // Sort by date descending (newest first)
                usort($files, fn($a, $b) => $b['last_modified'] <=> $a['last_modified']);
            }
        } catch (\Exception $e) {
            // R2 might not be configured yet
            session()->flash('error', 'No se pudo conectar a Cloudflare R2. Verifica las credenciales.');
        }

        return Inertia::render('Admins/Backups/Index', [
            'backups' => $files,
            'lastBackup' => $files[0] ?? null,
        ]);
    }

    /**
     * Create a new backup.
     */
    public function store(Request $request)
    {
        try {
            Artisan::call('backup:run', ['--only-db' => false]);
            
            return redirect()->route('backups.index')->with('success', 'Backup creado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('backups.index')->with('error', 'Error al crear backup: ' . $e->getMessage());
        }
    }

    /**
     * Download a backup file.
     */
    public function download(Request $request)
    {
        $path = $request->input('path');
        $disk = Storage::disk('r2');

        if (!$disk->exists($path)) {
            return redirect()->route('backups.index')->with('error', 'El archivo no existe.');
        }

        return $disk->download($path);
    }

    /**
     * Delete a backup file.
     */
    public function destroy(Request $request)
    {
        $path = $request->input('path');
        $disk = Storage::disk('r2');

        if (!$disk->exists($path)) {
            return redirect()->route('backups.index')->with('error', 'El archivo no existe.');
        }

        $disk->delete($path);

        return redirect()->route('backups.index')->with('success', 'Backup eliminado exitosamente.');
    }

    /**
     * Run cleanup of old backups.
     */
    public function cleanup()
    {
        try {
            Artisan::call('backup:clean');
            
            return redirect()->route('backups.index')->with('success', 'Limpieza de backups completada.');
        } catch (\Exception $e) {
            return redirect()->route('backups.index')->with('error', 'Error en limpieza: ' . $e->getMessage());
        }
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
