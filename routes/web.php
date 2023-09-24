<?php

use App\Http\Controllers\ShopController;
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

    Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
    Route::get('/inventory', [ShopController::class, 'inventory'])->name('shop.inventory');
    Route::get('/slides', [SlideController::class, 'manage'])->name('slides.manage');

    Route::get('/slides/{slide}/tmp-preview/', [SlideController::class, 'preview'])->name('slides.preview')->middleware('throttle:10,1');
    Route::post('/slides/activate-new', [SlideController::class, 'activateNew'])->name('slides.activateNew')->middleware('throttle:10,1');
    Route::post('/slides/{slide}/deactivate', [SlideController::class, 'deactivate'])->name('slides.deactivate')->middleware('throttle:10,1');
});

Route::get('/screen/{screen}', [SlideController::class, 'slideShow'])->name('slides.slideShow');

Route::get('/', function () {
    return view('app.home');
})->name('home');
