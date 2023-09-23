<?php

namespace App\Models;

use App\Events\SlideChanged;
use App\Events\SlideDeleted;
use App\Jobs\RemovePreviewSlide;
use App\Models\Scopes\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Slide extends Model
{
    use HasFactory;
    use Searchable;

    const STORAGE_DISK = 'local';
    const FILE_DIRECTORY = 'slides';

    const PREVIEW_STORAGE_DISK = 'public';
    const PREVIEW_FILE_DIRECTORY = 'slides-preview';
    const PREVIEW_LIFETIME_IN_SECONDS = 0;

    // Only plain HTML files are allowed
    const ACCEPTED_FILE_TYPES = [
        'text/html',
    ];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'deleted' => SlideDeleted::class,
        'updated' => SlideChanged::class,
    ];

    protected $fillable = [
        'title',
        'path',
        'user_id',
    ];

    protected $with = [
        'user',
    ];

    protected $searchableFields = ['*'];

    /**
     *
     * Scopes
     *
     */

    /**
     * Scope a query to only include approved slides.
     */
    public function scopeOnlyApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    /**
     * Extract the provided slide to a randomly generated public path
     */
    public function extractPreviewToPublic()
    {
        do
        {
            $randomString = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 20);
            $publicPath = self::PREVIEW_FILE_DIRECTORY . '/' . $randomString . '.html';
        } while (Storage::disk('public')->exists($publicPath));

        // Copy the file to the public disk
        $source = Storage::disk(self::STORAGE_DISK)->readStream($this->path);
        Storage::disk(self::PREVIEW_STORAGE_DISK)->writeStream($publicPath, $source);

        $cleanupJob = RemovePreviewSlide::dispatch(self::PREVIEW_STORAGE_DISK, $publicPath);

        if (self::PREVIEW_LIFETIME_IN_SECONDS > 0) {
            $cleanupJob->delay(now()->addSeconds(self::PREVIEW_LIFETIME_IN_SECONDS));
        }

        return asset('storage/' . $publicPath);
    }

    /**
     * Delete the file associated with this slide
     */
    public function deleteFile($originalPath = null)
    {
        $path = $originalPath ?? $this->path;

        Storage::disk(self::STORAGE_DISK)
            ->delete($path);
    }

    /**
     *
     * Relationships
     *
     */

    /**
     * The user this slide belongs to
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The teacher that  this slide
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * The screens this slide is active on
     */
    public function screens()
    {
        return $this->belongsToMany(Screen::class, 'screen_slides')
            ->using(ScreenSlide::class)
            ->withPivot([
                'activator_id',
                'slide_order',
                'slide_duration',
                'displays_from',
                'displays_until',
            ])
            ->withTimestamps();
    }

    /**
     * The active slides this slide is on
     */
    public function screenSlides()
    {
        return $this->hasMany(ScreenSlide::class);
    }
}
