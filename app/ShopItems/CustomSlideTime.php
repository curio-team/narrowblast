<?php

namespace App\ShopItems;

use App\Models\Screen;
use App\Models\ScreenSlide;
use App\Models\ShopItem;
use App\Models\ShopItemUser;
use App\Models\Slide;

#[ForShopItem('slide_7d')]
#[ForShopItem('slide_14d')]
class CustomSlideTime implements ShopItemInterface
{
    /**
     * Adds default time when a user purchases this item.
     */
    public static function onPurchase(ShopItem $shopItem, ShopItemUser $shopItemUser): void
    {
        $shopItemUser->data = array_merge($shopItemUser->data, [
            'time_total_in_seconds' => match ($shopItem->unique_id) {
                'slide_7d' => 7 * 24 * 60 * 60,
                'slide_14d' => 14 * 24 * 60 * 60,
            },
            'time_used_in_seconds' => 0,
        ]);
    }

    /**
     * Returns html to be displayed in the inventory showing how much time is left
     */
    public static function showUserData(ShopItem $shopItem, ShopItemUser $shopItemUser): string
    {
        $timeTotalInHours = $shopItemUser->data['time_total_in_seconds'] / 60 / 60;
        $timeUsedInHours = $shopItemUser->data['time_used_in_seconds'] / 60 / 60;
        $activeSlideId = $shopItemUser->data['active_slide'] ?? null;
        $activeSlide = $activeSlideId ? $shopItemUser->user->slides()->find($activeSlideId) : null;
        $selectableSlides = auth()->user()->approvedSlides()->withCount('screens')->where('screens_count', 0);

        return view('app.shop.items.custom-slide-time', compact('timeUsedInHours', 'timeTotalInHours', 'activeSlide', 'shopItemUser', 'selectableSlides'))->render();
    }

    /**
     * Sets the start time to now when a user uses this item.
     */
    public static function onUse(ShopItem $shopItem, ShopItemUser $shopItemUser, ...$arguments): null|\Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
    {
        // Guard against an invalid slide being passed
        $slide = $arguments[0];
        $isActivate = $arguments[1];

        if (!$slide || !is_object($slide) || get_class($slide) !== Slide::class) {
            throw new \Exception('No slide id passed to CustomSlideTime::onUse! Please call a developer.');
        }

        if (!is_bool($isActivate)) {
            throw new \Exception('No isActivate bool passed to CustomSlideTime::onUse! Please call a developer.');
        }

        if ($isActivate) {
            $timeLeft = $shopItemUser->data['time_total_in_seconds'] - $shopItemUser->data['time_used_in_seconds'];
            if ($timeLeft <= 0) {
                return redirect()->back()->withErrors([
                    'shop_item_user_id' => __('You have no time left on this item'),
                ]);
            }

            $screenSlide = new ScreenSlide;
            $screenSlide->slide_id = $slide->id;
            $screenSlide->activator_id = auth()->id();
            $screenSlide->displays_from = now();
            $screenSlide->displays_until = now()->addSeconds($timeLeft);

            foreach (Screen::all() as $screen) {
                $screenSlide->screen()->associate($screen);
            }

            $screenSlide->save();

            // Merge the active slide into the data
            $shopItemUser->data = array_merge($shopItemUser->data, [
                'active_slide' => $slide->id,
                'screen_slide_id' => $screenSlide->id,
                'active_slide_start_time' => time(),
            ]);
        } else {
            // Check that the user is using the correct slide
            if ($shopItemUser->data['active_slide'] !== $slide->id) {
                return redirect()->back()->withErrors([
                    'shop_item_user_id' => __('You are not using this slide with this item'),
                ]);
            }

            // Remove the active slide from the data
            $shopItemUser->data = array_merge($shopItemUser->data, [
                'active_slide' => null,
                'active_slide_start_time' => null,
            ]);

            if (isset($shopItemUser->data['screen_slide_id'])) {
                $screenSlide = ScreenSlide::find($shopItemUser->data['screen_slide_id']);

                if($screenSlide) {
                    $screenSlide->delete();
                }
            }
        }

        return null;
    }
}
