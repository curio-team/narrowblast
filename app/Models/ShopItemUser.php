<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ShopItemUser extends Pivot
{
    protected $fillable = [
        'user_id',
        'shop_item_id',
        'cost_in_credits',
    ];

    protected $casts = [
        'data' => AsArrayObject::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shopItem()
    {
        return $this->belongsTo(ShopItem::class);
    }
}
