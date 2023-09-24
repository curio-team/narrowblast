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

    Route::get('/admin/slide-preview/{slide}', [SlideController::class, 'preview'])->name('slides.preview')->middleware('auth.teacher');

    Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
    Route::get('/inventory', [ShopController::class, 'inventory'])->name('shop.inventory');
});

Route::get('/screen', [SlideController::class, 'slideShow'])->name('slides.slideShow');

Route::get('/', function () {
    return view('app.home');
})->name('home');
