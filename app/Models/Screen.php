<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Screen extends Model
{
    use HasFactory;

    protected $with = [
        'screenSlides',
    ];

    protected $fillable = [
        'name',
    ];

    /**
     *
     * Relationships
     *
     */
    public function screenSlides()
    {
        return $this->hasMany(ScreenSlide::class);
    }
}
