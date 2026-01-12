<?php

namespace App\Http\Controllers;

use App\Events\CommentCreated;
use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        // Permission Check for Clients
        if ($request->user()->company_id && !$request->user()->can('create_comments')) {
            abort(403, 'No tienes permiso para comentar.');
        }

        $validated = $request->validate([
            'body' => 'required|string',
            'commentable_id' => 'required|uuid',
            'commentable_type' => 'required|string|in:App\Models\Task,App\Models\Stage',
        ]);

        $comment = Comment::create([
            'user_id' => Auth::id(),
            'body' => $validated['body'],
            'commentable_id' => $validated['commentable_id'],
            'commentable_type' => $validated['commentable_type'],
        ]);

        // Broadcast to other users (optional - won't fail if Pusher/Reverb is not running)
        try {
            broadcast(new CommentCreated($comment->load('user')))->toOthers();
        } catch (\Exception $e) {
            // Silently fail if broadcast server is not available
            \Log::warning('Broadcasting failed: ' . $e->getMessage());
        }

        if ($request->user()->hasRole('client')) {
            try {
                // Get the commentable entity for context
                $commentableClass = $validated['commentable_type'];
                $commentable = $commentableClass::find($validated['commentable_id']);
                
                // Build a human-readable context
                $contextName = 'elemento';
                if ($commentable) {
                    $contextName = $commentable->name ?? $commentable->title ?? 'elemento';
                }
                
                // Type label
                $typeLabel = match($validated['commentable_type']) {
                    'App\\Models\\Task' => 'tarea',
                    'App\\Models\\Stage' => 'etapa',
                    default => 'elemento'
                };

                // Send the actual comment in the notification
                $message = "💬 Comentario en {$typeLabel} \"{$contextName}\": \"{$validated['body']}\"";
                
                event(new \App\Events\ClientActivityDetected(
                    $message,
                    $request->user()
                ));
            } catch (\Exception $e) {
                \Log::warning('Client activity broadcast failed: ' . $e->getMessage());
            }
        } else {
            // Logic for Admin commenting -> Notify Client
             try {
                // Get the commentable entity for context
                $commentableClass = $validated['commentable_type'];
                $commentable = $commentableClass::find($validated['commentable_id']);
                
                // Determine company_id to notify
                $companyId = null;
                // Try to traverse to project/company
                if ($commentable) {
                    if (method_exists($commentable, 'project')) {
                        // Task/Projec relationship
                        $companyId = $commentable->project->company_id ?? null;
                    } elseif ($commentable instanceof \App\Models\Project) {
                         $companyId = $commentable->company_id ?? null;
                    }
                     // If stage process to project
                     if ($validated['commentable_type'] === 'App\\Models\\Stage' && $commentable->project) {
                         $companyId = $commentable->project->company_id;
                     }
                     // If task process to stage -> project
                      if ($validated['commentable_type'] === 'App\\Models\\Task' && $commentable->stage && $commentable->stage->project) {
                         $companyId = $commentable->stage->project->company_id;
                     }
                }

                if ($companyId) {
                     // We need a generic notification event for clients. 
                     // For now, let's reuse ClientActivityDetected but directed to client channel?
                     // Or better, create a simple ClientNotification event.
                     // Let's use a new event: AdminResponseDetected
                     event(new \App\Events\AdminResponseDetected(
                        "Tienes un nuevo comentario de Admin",
                        $companyId
                     ));
                }

            } catch (\Exception $e) {
                \Log::warning('Admin response broadcast failed: ' . $e->getMessage());
            }
        }

        return back();
    }

    public function update(Request $request, Comment $comment)
    {
        // Only admin or comment owner can edit
        $isAdmin = !$request->user()->company_id;
        $isOwner = $comment->user_id === $request->user()->id;
        
        if (!$isAdmin && !$isOwner) {
            abort(403, 'No tienes permiso para editar este comentario.');
        }

        $validated = $request->validate([
            'body' => 'required|string',
        ]);

        try {
            broadcast(new \App\Events\CommentUpdated($comment))->toOthers();

            // Notify about update
            $user = $request->user();
            $message = "📝 Comentario editado en {$comment->commentable_type}"; // Simplified message
            
            // Build Context (reused logic)
            $commentable = $comment->commentable;
            $typeLabel = match($comment->commentable_type) {
                'App\\Models\\Task' => 'tarea',
                'App\\Models\\Stage' => 'etapa',
                default => 'elemento'
            };
            $contextName = $commentable->name ?? $commentable->title ?? 'elemento';
            $message = "📝 Comentario editado en {$typeLabel} \"{$contextName}\"";

            if ($user->company_id) {
                // Client edited -> Notify Admin
                 event(new \App\Events\ClientActivityDetected($message, $user));
            } else {
                // Admin edited -> Notify Client
                $companyId = null;
                if ($commentable) {
                     if (method_exists($commentable, 'project')) { $companyId = $commentable->project->company_id ?? null; }
                     elseif ($commentable instanceof \App\Models\Project) { $companyId = $commentable->company_id ?? null; }
                     if ($comment->commentable_type === 'App\\Models\\Stage' && $commentable->project) { $companyId = $commentable->project->company_id; }
                     if ($comment->commentable_type === 'App\\Models\\Task' && $commentable->stage && $commentable->stage->project) { $companyId = $commentable->stage->project->company_id; }
                }

                if ($companyId) {
                    event(new \App\Events\AdminResponseDetected("Admin editó un comentario en {$typeLabel}: \"{$contextName}\"", $companyId));
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Broadcasting failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Comentario actualizado.');
    }

    public function destroy(Request $request, Comment $comment)
    {
        // Only admin can delete comments
        if ($request->user()->company_id) {
            abort(403, 'No tienes permiso para eliminar comentarios.');
        }

        $id = $comment->id;
        $type = $comment->commentable_type;
        $commentableId = $comment->commentable_id;

        $comment->delete();

        try {
            broadcast(new \App\Events\CommentDeleted($id, $type, $commentableId))->toOthers();

             // Notify Client about deletion (Only admins delete)
             // Reconstruct context
             $commentableClass = $type;
             $commentable = $commentableClass::find($commentableId);
             
             if ($commentable) {
                // Determine logic for company ID
                $companyId = null;
                if (method_exists($commentable, 'project')) { $companyId = $commentable->project->company_id ?? null; }
                elseif ($commentable instanceof \App\Models\Project) { $companyId = $commentable->company_id ?? null; }
                if ($type === 'App\\Models\\Stage' && $commentable->project) { $companyId = $commentable->project->company_id; }
                if ($type === 'App\\Models\\Task' && $commentable->stage && $commentable->stage->project) { $companyId = $commentable->stage->project->company_id; }

                $typeLabel = match($type) {
                    'App\\Models\\Task' => 'tarea',
                    'App\\Models\\Stage' => 'etapa',
                    default => 'elemento'
                };
                $contextName = $commentable->name ?? $commentable->title ?? 'elemento';

                if ($companyId) {
                    event(new \App\Events\AdminResponseDetected("Admin eliminó un comentario en {$typeLabel}: \"{$contextName}\"", $companyId));
                }
             }

        } catch (\Exception $e) {
            \Log::warning('Broadcasting failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Comentario eliminado.');
    }
}
