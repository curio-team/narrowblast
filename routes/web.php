<?php

use App\Http\Controllers\InviteController;
use App\Http\Controllers\JavascriptPowerupController;
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
        Route::get('/credits', [ShopController::class, 'credits'])->name('shop.credits');

        Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
        Route::get('/inventory', [ShopController::class, 'inventory'])->name('shop.inventory');
        Route::get('/slides', [SlideController::class, 'manage'])->name('slides.manage');
        Route::get('/slides/upload', [SlideController::class, 'upload'])->name('slides.upload');

        Route::get('/slides/{slide}/tmp-preview/', [SlideController::class, 'preview'])->name('slides.preview');//->middleware('throttle:10,1');
        Route::post('/slides/activate-new', [SlideController::class, 'activateNew'])->name('slides.activateNew');//->middleware('throttle:10,1');
        Route::post('/slides/{slide}/deactivate', [SlideController::class, 'deactivate'])->name('slides.deactivate');//->middleware('throttle:10,1');

        Route::post('/slides/powerUpJavascript', [JavascriptPowerupController::class, 'powerUpJavascript'])->name('slides.powerUpJavascript');//->middleware('throttle:10,1');

        Route::post('/slides/inviteActivate', [InviteController::class, 'inviteActivate'])->name('slides.inviteActivate');//->middleware('throttle:10,1');
        Route::get('/slides/inviteEnter/{inviteCode?}', [InviteController::class, 'inviteEnter'])->name('slides.inviteEnter');//->middleware('throttle:10,1');
        Route::post('/slides/inviteEnter', [InviteController::class, 'inviteProcess'])->name('slides.inviteProcess');//->middleware('throttle:10,1');
        Route::post('/slides/inviteConfirm', [InviteController::class, 'inviteConfirm'])->name('slides.inviteConfirm');//->middleware('throttle:10,1');
        Route::get('/slides/inviteeInteract/{inviteSystem}', [InviteController::class, 'inviteeInteract'])->name('slides.inviteeInteract');//->middleware('throttle:10,1');
    });

Route::post('/slides/inviteCodeRequest', [InviteController::class, 'inviteCodeRequest'])->name('slides.inviteCodeRequest');//->middleware('throttle:10,1');
Route::post('/slides/inviteCodeUpdate', [InviteController::class, 'inviteCodeUpdate'])->name('slides.inviteCodeUpdate');//->middleware('throttle:10,1');
Route::post('/slides/inviteRedistributeRequest', [InviteController::class, 'inviteRedistributeRequest'])->name('slides.inviteRedistributeRequest');//->middleware('throttle:10,1');
Route::post('/slides/inviteRequestSetInteractionData', [InviteController::class, 'inviteRequestSetInteractionData'])->name('slides.inviteRequestSetInteractionData');//->middleware('throttle:10,1');

Route::post('/screen/{screen}/tick', [SlideController::class, 'slideShowTick'])->name('slides.slideShowTick');
Route::get('/screen/{screen}', [SlideController::class, 'slideShow'])->name('slides.slideShow');

Route::get('/', function () {
    return view('app.home');
})->name('home');
