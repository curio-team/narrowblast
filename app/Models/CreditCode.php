<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreditCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'credits',
        'redeemed_at',
        'redeemed_by',
        'created_by',
    ];

    protected $casts = [
        'redeemed_at' => 'datetime',
    ];

    public static function generateCode()
    {
        $code = strtoupper(\Str::random(16));
        if (static::where('code', $code)->exists()) {
            return static::generateCode();
        }
        return $code;
    }

    public function redeem(User $user)
    {
        DB::transaction(function () use ($user) {
            $this->update([
                'redeemed_at' => now(),
                'redeemed_by' => $user->id,
            ]);
            $user->credits += $this->credits;
            $user->save();
        });
    }

    public function redeemer()
    {
        return $this->belongsTo(User::class, 'redeemed_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeUnredeemed($query)
    {
        return $query->whereNull('redeemed_at');
    }

    public function scopeRedeemed($query)
    {
        return $query->whereNotNull('redeemed_at');
    }
}
