<?php

namespace App\Jobs;

use App\Models\Slide;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class RemovePreviewSlide implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public string $previewDisk,
        public string $previewPath,
    ) { }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $disk = Storage::disk($this->previewDisk);
        if ($disk->exists($this->previewPath))
            $disk->delete($this->previewPath);
    }
}
