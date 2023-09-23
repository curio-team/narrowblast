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
}
