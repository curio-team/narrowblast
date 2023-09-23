<?php

use App\Http\Controllers\SlideController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * AmoClient Auth
 */
Route::get('/login', function(){
	return redirect('/amoclient/redirect');
})->name('login');

Route::post('/logout', function(Request $request){
    Auth::logout();

	return redirect('/amoclient/logout');
})->name('logout');

Route::get('/amoclient/ready', function(){
	return redirect()->route('home');
});

/**
 * App
 */
Route::prefix('/')
    ->middleware('auth')
    ->group(function () {

    // Route::resource('manage', SlideController::class, [
    //     'names' => 'slides'
    // ])->parameters([
    //     'manage' => 'slide'
    // ])->except([
    //     'show'
    // ])->middleware('auth.teacher');
    Route::get('/admin/slide-preview/{slide}', [SlideController::class, 'preview'])->name('slides.preview')->middleware('auth.teacher');
});

Route::middleware('auth.test')->group(function () {
    // Route::get('/test', [SlideController::class, 'testShow'])->name('slides.test.show');
    // Route::post('/test', [SlideController::class, 'testSubmit'])->name('slides.test.submit');
    // Route::get('/test/submitted', [SlideController::class, 'testSubmitted'])->name('slides.test.submitted');
});

Route::get('/screen', [SlideController::class, 'slideShow'])->name('slides.slideShow');

Route::get('/', function () {
    return view('home');
})->name('home');
