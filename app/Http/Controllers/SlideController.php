<?php

namespace App\Http\Controllers;

use App\Models\Screen;
use App\Models\ShopItemUser;
use App\Models\Slide;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule as ValidationRule;

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
    public function slideShow(Screen $screen)
    {
        return view('app.slides.slide-show', [
            'screen' => $screen,
        ]);
    }

    /**
     * API endpoint to get updates on active slides and to call the respective callShopItemMethod on the shop items
     */
    public function slideShowTick(Request $request, Screen $screen)
    {
        $screenSlides = $screen->screenSlides;
        $slidePublicPaths = [];
        $shopItemUsers = ShopItemUser::where(function(Builder $query) use ($screenSlides) {
            $query->whereIn('data->screen_slide_id', $screenSlides->pluck('id'));
        })->get();

        foreach ($shopItemUsers as $shopItemUser) {
            // Get the associated screen slide for this shop item user
            $screenSlide = $screenSlides->firstWhere('id', $shopItemUser->data['screen_slide_id']);

            if ($screenSlide) {
                // Call the onTick method on the shopItem
                $shopItem = $shopItemUser->shopItem;
                $result = $shopItem->callShopItemMethod('onTick', $shopItemUser, $screenSlide);

                if ($result !== false) {
                    $slidePublicPaths[] = asset('storage/'.$screenSlide->slide->getKnownPath());
                }
            }
        }

        return response()->json([
            'public_paths' => $slidePublicPaths,
        ]);
    }

    /**
     * Display a users slide management page
     */
    public function manage()
    {
        return view('app.slides.manage');
    }

    /**
     * Activate a new slide for the user
     */
    public function activateNew(Request $request)
    {
        $request->validate([
            'slide_id' => [
                'required',
                ValidationRule::exists('slides', 'id')->where(function ($query) {
                    $query->whereIn('id', auth()->user()->approvedSlides()->pluck('id'));
                }),
            ],
            'shop_item_user_id' => [
                'required',
                'exists:shop_item_user,id',
            ],
        ]);

        // Check that the user owns the given shop item
        $shopItemUser = auth()->user()->shopItemUsers()->find($request->shop_item_user_id);

        if (!$shopItemUser) {
            return redirect()->back()->withErrors([
                'shop_item_user_id' => __('You do not own this item'),
            ]);
        }

        $slide = Slide::find($request->slide_id);

        $result = $shopItemUser->shopItem->callShopItemMethod('onUse', $shopItemUser, $slide, true);

        if ($result !== null) {
            return $result;
        }

        $shopItemUser->save();

        return redirect()->back()->with('success', 'Slide activated!');
    }

    /**
     * Deactivate the provided for the user
     */
    public function deactivate(Request $request, Slide $slide)
    {
        $request->validate([
            'shop_item_user_id' => [
                'required',
                'exists:shop_item_user,id',
            ],
        ]);

        // Check that the user owns the given shop item
        $shopItemUser = auth()->user()->shopItemUsers()->find($request->shop_item_user_id);

        if (!$shopItemUser) {
            return redirect()->back()->withErrors([
                'shop_item_user_id' => __('You do not own this item'),
            ]);
        }

        $result = $shopItemUser->shopItem->callShopItemMethod('onUse', $shopItemUser, $slide, false);

        if ($result !== null) {
            return $result;
        }

        $shopItemUser->save();

        return redirect()->back()->with('success', 'Slide deactivated!');
    }
}
