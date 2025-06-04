<?php

namespace App\Policies;

use App\Models\Digitalization;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use function auth;

class DigitalizationPolicy
{
    /**
     * Determine whether the user can view any models.
     */

    public function download(Digitalization $digitalization)
    {
        return auth()->user()->id === $digitalization->user_id;
    }

}
