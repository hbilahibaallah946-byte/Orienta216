<?php

namespace App\Policies;

use App\Models\User;

class UniversityPdfPolicy
{
    public function managePdf(User $user): bool
    {
        return $user->role === 'conseiller';
    }
}