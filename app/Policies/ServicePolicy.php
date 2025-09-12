<?php

namespace App\Policies;

use App\Models\Services;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ServicePolicy
{
    public function update(User $user, Services $services)
    {
        return $user->role === 'admin'; // Seul l'admin peut modifier
    }

    public function delete(User $user, Services $services)
    {
        return $user->role === 'admin';
    }

}
