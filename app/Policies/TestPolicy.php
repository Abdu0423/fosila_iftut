<?php

namespace App\Policies;

use App\Models\Test;
use App\Models\User;

class TestPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Test $test): bool
    {
        // Учитель может просматривать тест, если он владелец расписания
        return $test->schedule && $test->schedule->teacher_id === $user->id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Test $test): bool
    {
        if ($user->isAdmin() || $user->isEducationDepartment() || $user->isRegistrationCenter()) {
            return true;
        }

        return $test->schedule && $test->schedule->teacher_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Test $test): bool
    {
        // Учитель может удалять тест, если он владелец расписания
        return $test->schedule && $test->schedule->teacher_id === $user->id;
    }
}
