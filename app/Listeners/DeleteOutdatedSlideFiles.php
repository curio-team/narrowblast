<?php

namespace App\Listeners;

use App\Events\SlideChanged;
use App\Models\Slide;
use Illuminate\Support\Facades\Storage;

class DeleteOutdatedSlideFiles
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  SlideChanged  $event
     * @return void
     */
    public function handle(SlideChanged $event)
    {
        /** @var Slide $slide */
        $slide = $event->slide;

        $slideExists = $slide->exists;
        $slideFileChanged = $slide->getOriginal('path') !== $slide->path;

        if (!$slideExists || $slideFileChanged) {
            $slide->deleteFile($slide->getOriginal('path'));
        }
    }
}
