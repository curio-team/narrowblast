<?php

namespace App\Http\Controllers;

use App\Models\Slide;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule as ValidationRule;

class JavascriptPowerupController extends Controller
{

    /**
     * Activates JavaScript capabilities for the given slide
     */
    public function powerUpJavascript(Request $request)
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

        if ($shopItemUser->shopItem->unique_id !== 'slide_powerup_js') {
            return redirect()->back()->withErrors([
                'shop_item_user_id' => __('This item is not a JavaScript power up'),
            ]);
        }

        $slide = Slide::find($request->slide_id);
        $result = $shopItemUser->shopItem->callShopItemMethod('onUse', $shopItemUser, $slide);

        if ($result !== null) {
            return $result;
        }

        $shopItemUser->save();

        return redirect()->back()->with('success', 'Javascript op slide geactiveerd!');
    }
}
