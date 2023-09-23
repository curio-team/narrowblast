<?php

namespace App\Policies;

use App\Models\ShopItem;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShopItemPolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  App\Models\User  $user
     * @param  App\Models\ShopItem  $model
     * @return mixed
     */
    public function view(User $user, ShopItem $model)
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  App\Models\User  $user
     * @param  App\Models\ShopItem  $model
     * @return mixed
     */
    public function update(User $user, ShopItem $model)
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  App\Models\User  $user
     * @param  App\Models\ShopItem  $model
     * @return mixed
     */
    public function delete(User $user, ShopItem $model)
    {
        return false;
    }

    /**
     * Determine whether the user can delete multiple instances of the model.
     *
     * @param  App\Models\User  $user
     * @param  App\Models\ShopItem  $model
     * @return mixed
     */
    public function deleteAny(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  App\Models\User  $user
     * @param  App\Models\ShopItem  $model
     * @return mixed
     */
    public function restore(User $user, ShopItem $model)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  App\Models\User  $user
     * @param  App\Models\ShopItem  $model
     * @return mixed
     */
    public function forceDelete(User $user, ShopItem $model)
    {
        return false;
    }
}
