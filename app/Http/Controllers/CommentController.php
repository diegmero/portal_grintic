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
        }

        return back();
    }
}
