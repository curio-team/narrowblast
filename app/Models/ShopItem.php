<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopItem extends Model
{
    use HasFactory;

    const STORAGE_DISK = 'public';
    const FILE_DIRECTORY = 'shop_items';

    protected $fillable = [
        'name',
        'description',
        'image_path',
        'cost_in_credits',
        'max_per_user',
    ];

    public function userHasMaximum(User $user) {
        return $this->max_per_user !== null
            && $this->shopItemUsers()->where('user_id', auth()->id())->count() >= $this->max_per_user;
    }

    /**
     *
     * Relationships
     *
     */

    public function shopItemUsers() {
        return $this->hasMany(ShopItemUser::class);
    }
}
