<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Comments channel - handles App.Models.Task.{uuid} and App.Models.Stage.{uuid}
// The type is split into multiple segments due to dot notation
Broadcast::channel('comments.App.Models.Task.{id}', function ($user, $id) {
    \Log::info("Auth check for comments.App.Models.Task.{$id} - User: {$user->id}");
    return true; // Any authenticated user can listen
});

Broadcast::channel('comments.App.Models.Stage.{id}', function ($user, $id) {
    \Log::info("Auth check for comments.App.Models.Stage.{$id} - User: {$user->id}");
    return true; // Any authenticated user can listen
});

Broadcast::channel('admin.alerts', function ($user) {
    return $user->hasRole('admin');
});
