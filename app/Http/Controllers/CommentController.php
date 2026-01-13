<?php

namespace App\Http\Controllers;

use App\Events\CommentCreated;
use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewCommentNotification;
use App\Notifications\CommentActivityNotification;

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
                
                // Context
                $typeLabel = match($validated['commentable_type']) {
                    'App\\Models\\Task' => 'tarea',
                    'App\\Models\\Stage' => 'etapa',
                    default => 'elemento'
                };
                $context = ['type_label' => $typeLabel, 'name' => $contextName];

                $admins = \App\Models\User::whereNull('company_id')->get();
                if ($admins->count() > 0) {
                     Notification::send($admins, new \App\Notifications\NewCommentNotification($comment, $request->user(), $context));
                }

            } catch (\Exception $e) {
                \Log::warning('Client notification failed: ' . $e->getMessage());
            }
        } else {
            // Logic for Admin commenting -> Notify Client Users
             try {
                // Get commentable
                $commentableClass = $validated['commentable_type'];
                $commentable = $commentableClass::find($validated['commentable_id']);
                
                // Determine company_id to notify
                $companyId = null;
                if ($commentable) {
                    if (method_exists($commentable, 'project')) { $companyId = $commentable->project->company_id ?? null; }
                    elseif ($commentable instanceof \App\Models\Project) { $companyId = $commentable->company_id ?? null; }
                    if ($validated['commentable_type'] === 'App\\Models\\Stage' && $commentable->project) { $companyId = $commentable->project->company_id; }
                    if ($validated['commentable_type'] === 'App\\Models\\Task' && $commentable->stage && $commentable->stage->project) { $companyId = $commentable->stage->project->company_id; }
                }

                if ($companyId) {
                     // Get type label
                     $typeLabel = match($validated['commentable_type']) {
                        'App\\Models\\Task' => 'tarea',
                        'App\\Models\\Stage' => 'etapa',
                        default => 'elemento'
                    };
                    $contextName = $commentable->name ?? $commentable->title ?? 'elemento';
                    $context = ['type_label' => $typeLabel, 'name' => $contextName];
                    
                    $clientUsers = \App\Models\User::where('company_id', $companyId)->get();
                    if ($clientUsers->count() > 0) {
                        Notification::send($clientUsers, new \App\Notifications\NewCommentNotification($comment, $request->user(), $context));
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Admin notification failed: ' . $e->getMessage());
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
        $comment->update(['body' => $validated['body']]);

        try {
            broadcast(new \App\Events\CommentUpdated($comment))->toOthers();

            // Notification Logic using Laravel Notifications (Database + Broadcast)
            $user = $request->user();
            
            // Reused context logic
            $commentable = $comment->commentable;
            $typeLabel = match($comment->commentable_type) {
                'App\\Models\\Task' => 'tarea',
                'App\\Models\\Stage' => 'etapa',
                default => 'elemento'
            };
            $contextName = $commentable->name ?? $commentable->title ?? 'elemento';
            
            $context = ['type_label' => $typeLabel, 'name' => $contextName];

            if ($user->company_id) {
                 // Client edited -> Notify Admins
                 $admins = \App\Models\User::whereNull('company_id')->get();
                 if ($admins->count() > 0) {
                     Notification::send($admins, new CommentActivityNotification('updated', $user, $context, $validated['body']));
                 }
            } else {
                // Admin edited -> Notify Client Users
                $companyId = null;
                if ($commentable) {
                     if (method_exists($commentable, 'project')) { $companyId = $commentable->project->company_id ?? null; }
                     elseif ($commentable instanceof \App\Models\Project) { $companyId = $commentable->company_id ?? null; }
                     if ($comment->commentable_type === 'App\\Models\\Stage' && $commentable->project) { $companyId = $commentable->project->company_id; }
                     if ($comment->commentable_type === 'App\\Models\\Task' && $commentable->stage && $commentable->stage->project) { $companyId = $commentable->stage->project->company_id; }
                }

                if ($companyId) {
                    $clientUsers = \App\Models\User::where('company_id', $companyId)->get();
                    if ($clientUsers->count() > 0) {
                        Notification::send($clientUsers, new CommentActivityNotification('updated', $user, $context, $validated['body']));
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Notification failed: ' . $e->getMessage());
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
        $bodyForNotification = $comment->body; // Capture body before delete
        $user = $request->user(); // Admin

        $comment->delete();

        try {
            broadcast(new \App\Events\CommentDeleted($id, $type, $commentableId))->toOthers();

             // Notify Client about deletion
             $commentableClass = $type;
             $commentable = $commentableClass::find($commentableId);
             
             if ($commentable) {
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
                $context = ['type_label' => $typeLabel, 'name' => $contextName];

                if ($companyId) {
                    $clientUsers = \App\Models\User::where('company_id', $companyId)->get();
                    if ($clientUsers->count() > 0) {
                        Notification::send($clientUsers, new CommentActivityNotification('deleted', $user, $context, $bodyForNotification));
                    }
                }
             }

        } catch (\Exception $e) {
            \Log::warning('Notification failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Comentario eliminado.');
    }
}
