<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Allow authenticated users to listen to comments on tasks/stages they have access to.
// Simplifying permission check for now: just must be auth.
Broadcast::channel('comments.{type}.{id}', function ($user, $type, $id) {
    return true; 
});

Broadcast::channel('admin.alerts', function ($user) {
    return $user->hasRole('admin');
});
