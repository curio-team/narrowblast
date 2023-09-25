<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InviteSystem extends Model
{
    use HasFactory;
    use HasUuids;

    const INVITE_CODE_CHARACTERS = '123456789';
    const INVITE_CODE_LENGTH = 6;

    protected $casts = [
        'data' => AsArrayObject::class,
    ];

    /**
     * Generates a random, non existing code.
     */
    function generateCode()
    {
        do {
            $code = '';
            for ($i = 0; $i < self::INVITE_CODE_LENGTH; $i++) {
                $code .= self::INVITE_CODE_CHARACTERS[rand(0, strlen(self::INVITE_CODE_CHARACTERS) - 1)];
            }
        } while ($this->codeExists($code));

        return $code;
    }

    /**
     * Checks if the code exists in the database.
     */
    function codeExists($code)
    {
        return $this->where('latest_code', $code)->exists();
    }

    function formatCode()
    {
        return implode('-', str_split($this->latest_code, self::INVITE_CODE_LENGTH * .5));
    }

    function isPreview()
    {
        return $this->shop_item_user_id == null;
    }

    /**
     *
     * Relationships
     *
     */

    public function shopItem()
    {
        return $this->belongsTo(ShopItem::class);
    }

    public function shopItemUser()
    {
        return $this->belongsTo(ShopItemUser::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invitees()
    {
        return $this->hasMany(InviteSystemInvitee::class);
    }
}
