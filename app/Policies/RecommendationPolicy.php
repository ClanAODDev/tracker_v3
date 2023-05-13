<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Recommendation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RecommendationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewForDivision(User $actor, $divisionId): bool
    {
        if ($actor->role == Role::MEMBER) {
            return false;
        }

        if (in_array($actor->role, [Role::OFFICER, Role::JUNIOR_LEADER])) {
            if ($actor->division->id != $divisionId) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Recommendation $promotion
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Recommendation $promotion)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Recommendation $promotion
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Recommendation $promotion)
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Recommendation $promotion
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Recommendation $promotion)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Recommendation $promotion
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Recommendation $promotion)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Recommendation $promotion
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Recommendation $promotion)
    {
        //
    }
}
