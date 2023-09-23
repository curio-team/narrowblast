<?php

namespace App\Http\Controllers;

use App\Models\ActiveSlide;
use App\Models\Slide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SlideController extends Controller
{
    /**
     * Display a preview of the slide
     */
    public function preview(Slide $slide)
    {
        $publicPath = $slide->extractPreviewToPublic();

        return view('app.slides.preview', [
            'slide' => $slide,
            'publicPath' => $publicPath,
        ]);
    }

    /**
     * Display all currently active slides (updated from JavaScript every 5 seconds)
     */
    public function slideShow()
    {
        $activeSlides = ActiveSlide::all();

        return view('app.slides.slide-show', [
            'activeSlides' => $activeSlides,
        ]);
    }
}
