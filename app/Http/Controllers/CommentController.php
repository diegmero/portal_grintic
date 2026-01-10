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

        broadcast(new CommentCreated($comment->load('user')))->toOthers();

        if ($request->user()->hasRole('client')) {
            event(new \App\Events\ClientActivityDetected(
                "Cliente comentó en: {$validated['commentable_type']} #{$validated['commentable_id']}",
                $request->user()
            ));
        }

        return back();
    }
}
