<?php

namespace App\Policies;

use App\Models\DigitalizationBatch;
use App\Models\User;
use function is_null;

class DigitalizationBatchPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function download(User $user, DigitalizationBatch $digitalizationBatch): bool
    {
        if (is_null($digitalizationBatch->user_id) && auth()->check()) {
            return true;
        }
        return $user->id === $digitalizationBatch->user_id;
    }

    public function view(?User $user, DigitalizationBatch $digitalizationBatch): bool
    {
        if (is_null($digitalizationBatch->user_id)) {
            return true;
        }

        return !is_null($user) && $user->id === $digitalizationBatch->user_id;
    }

    public function destroy(User $user, DigitalizationBatch $digitalizationBatch): bool
    {
        return $user->id === $digitalizationBatch->user_id;
    }
}
