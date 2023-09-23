<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'receiver_id',
        'sender_id',
        'amount',
        'reason',
    ];

    /**
     * Starts a transaction for mutating the credits of a user and logs the transaction
     *
     * @param  App\Models\User  $receiver
     * @param  App\Models\User|null  $sender
     * @param  int  $amount
     * @param  string  $reason
     * @param  \Closure $mutator
     * @return void
     */
    public static function mutateWithTransaction(User $receiver, ?User $sender, int $amount, string $reason, \Closure $mutator): void
    {
        \DB::transaction(function () use ($receiver, $sender, $amount, $reason, $mutator) {
            $log = self::create([
                'receiver_id' => $receiver->id,
                'sender_id' => $sender?->id,
                'amount' => $amount,
                'reason' => $reason,
            ]);

            $mutator($log);
        });
    }

    /**
     *
     * Relationships
     *
     */

    public function receiver()
    {
        return $this->belongsTo(User::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class);
    }
}
