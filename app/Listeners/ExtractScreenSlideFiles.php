<?php

namespace App\Listeners;

use App\Events\ScreenSlideCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ExtractScreenSlideFiles
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
     * @param  ScreenSlideCreated  $event
     * @return void
     */
    public function handle(ScreenSlideCreated $event)
    {
        $screenSlide = $event->screenSlide;

        // Note: this will override if another ScreenSlide is using the same slide. But that shouldn't matter since it's the exact same file.
        $screenSlide->slide->extractLiveToPublic();
    }
}
