<?php

namespace App\Models;

use App\Models\Scopes\Searchable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Filament\Panel;
use Filament\Models\Contracts\FilamentUser;

class User extends Authenticatable implements FilamentUser
{
    use Notifiable;
    use HasFactory;
    use Searchable;

    protected $fillable = ['name', 'email', 'password'];

    protected $searchableFields = ['*'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public $incrementing = false;

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isSuperAdmin();
    }

    public function isSuperAdmin()
    {
        return $this->isTeacher() || in_array($this->email, config('auth.super_admins'));
    }

    public function isTeacher()
    {
        return $this->type === 'teacher';
    }

    public function getFormattedCredits()
    {
        return number_format($this->credits, 0, ',', '.');
    }

    /**
     *
     * Relationships
     *
     */

    /**
     * The slides that this user has uploaded
     */
    public function slides()
    {
        return $this->hasMany(Slide::class, 'user_id');
    }

    /**
     * The slides that this user has
     */
    public function approvedSlides()
    {
        return $this->hasMany(Slide::class, 'approver_id');
    }

    /**
     * The items that this user has purchased
     */
    public function purchasedShopItems()
    {
        return $this->belongsToMany(ShopItem::class, 'shop_item_user', 'user_id', 'shop_item_id')
            ->withPivot('id', 'cost_in_credits', 'data')
            ->using(ShopItemUser::class)
            ->withTimestamps();
    }

    /**
     * The pivots for items that this user has purchased
     */
    public function shopItemUsers()
    {
        return $this->hasMany(ShopItemUser::class, 'user_id');
    }
}
