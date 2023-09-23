<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Screen extends Model
{
    use HasFactory;

    protected $with = [
        'slides',
    ];

    protected $fillable = [
        'name',
    ];

    /**
     *
     * Relationships
     *
     */
    public function slides()
    {
        return $this->hasMany(ScreenSlide::class);
    }
}
