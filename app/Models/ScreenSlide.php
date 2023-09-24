<?php

namespace App\Models;

use App\Events\ScreenSlideCreated;
use App\Events\ScreenSlideDeleted;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScreenSlide extends Model
{
    use HasFactory;

    const FILE_ACTIVE_DIRECTORY = 'slides-active';

    protected $with = [
        'slide',
    ];

    protected $fillable = [
        'slide_id',
        'screen_id',
        'activator_id',
        'slide_duration',
        'displays_from',
        'displays_until',
        'slide_order',
    ];

    protected $casts = [
        'displays_from' => 'datetime',
        'displays_until' => 'datetime',
    ];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'deleted' => ScreenSlideDeleted::class,
        'created' => ScreenSlideCreated::class,
    ];

    /**
     *
     * Relationships
     *
     */

    /**
     * Which screen this slide is active on
     */
    public function screen()
    {
        return $this->belongsTo(Screen::class);
    }

    /**
     * Which slide this active slide is in reference to
     */
    public function slide()
    {
        return $this->belongsTo(Slide::class);
    }

    /**
     * The user who set this slide as active
     */
    public function activator()
    {
        return $this->belongsTo(User::class, 'activator_id');
    }
}
