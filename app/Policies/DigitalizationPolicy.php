<?php

namespace App\Policies;

use App\Models\Digitalization;
use App\Models\User;

class DigitalizationPolicy
{
    /**
     * Determine whether the user can view any models.
     */

    public function download(User $user, Digitalization $digitalization): bool
    {
        return $user->id === $digitalization->user_id;
    }

    public function view(User $user, Digitalization $digitalization): bool
    {
        return $user->id === $digitalization->user_id;
    }

    public function destroy(User $user, Digitalization $digitalization): bool
    {
        return $user->id === $digitalization->user_id;
    }

}
