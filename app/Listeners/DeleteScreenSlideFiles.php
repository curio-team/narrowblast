<?php

namespace App\Listeners;

use App\Events\ScreenSlideDeleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteScreenSlideFiles
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    { }

    /**
     * Handle the event.
     *
     * @param  ScreenSlideDeleted  $event
     * @return void
     */
    public function handle(ScreenSlideDeleted $event)
    {
        $slide = $event->screenSlide->slide;

        // If no other screen slide is using the same slide, delete the live file
        if ($slide->screenSlides()->count() === 0) {
            $slide->deleteLiveFile();
        }
    }
}
