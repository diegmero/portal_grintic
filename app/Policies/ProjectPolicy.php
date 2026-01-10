<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->company_id !== null;
    }

    public function view(User $user, Project $project): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }
        return $user->company_id === $project->company_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Project $project): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->hasRole('admin');
    }
}
