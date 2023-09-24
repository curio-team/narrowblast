<?php

namespace App\ShopItems;

use App\Models\Screen;
use App\Models\ScreenSlide;
use App\Models\ShopItem;
use App\Models\ShopItemUser;
use App\Models\Slide;
use Illuminate\Contracts\Database\Query\Builder;

#[ForShopItem('slide_powerup_js')]
class JavascriptSlide implements ShopItemInterface
{
    /**
     * Adds default time when a user purchases this item.
     */
    public static function onPurchase(ShopItem $shopItem, ShopItemUser $shopItemUser): void
    {
        $shopItemUser->data['uses_total'] = 1;
        $shopItemUser->data['slide_ids'] = [];
    }

    /**
     * Returns html to be displayed in the inventory showing how much time is left
     */
    public static function showUserData(ShopItem $shopItem, ShopItemUser $shopItemUser): string
    {
        $usesTotal = $shopItemUser->data['uses_total'];
        $usedOnSlides = $shopItemUser->data['slide_ids'];
        $selectableSlides = auth()->user()->approvedSlides()->where(function(Builder $query) {
            $query->where('data->has_javascript_powerup', false)
                ->orWhereNull('data->has_javascript_powerup');
        })->get();

        return view('app.shop.items.javascript-slide', compact('usesTotal', 'usedOnSlides', 'selectableSlides', 'shopItemUser'))->render();
    }

    /**
     * Sets the start time to now when a user uses this item.
     */
    public static function onUse(ShopItem $shopItem, ShopItemUser $shopItemUser, ...$arguments): null|\Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
    {
        // Guard against an invalid slide being passed
        $slide = $arguments[0];

        if (!$slide || !is_object($slide) || get_class($slide) !== Slide::class) {
            throw new \Exception('No slide id passed to CustomSlideTime::onUse! Please call a developer.');
        }

        // Check if the slide has already been used
        if ($slide->data && $slide->data['has_javascript_powerup']) {
            return redirect()->back()->withErrors([
                'error' => 'This slide has already been powered up with JavaScript!',
            ]);
        }

        $shopItemUser->data['slide_ids'][] = $slide->id;
        $slide->setData('has_javascript_powerup', true);

        return null;
    }

    /**
     * Called periodically while the product is being used.
     *
     * We use this to check if the item has expired.
     */
    public static function onTick(ShopItem $shopItem, ShopItemUser $shopItemUser, ...$arguments): bool
    {
        return true;
    }

    /**
     * Called to get custom slide column for the slides table
     */
    public static function getCustomSlideColumns(ShopItem $shopItem, ShopItemUser $shopItemUser, bool $isApproved): false|array
    {
        if (!$isApproved) {
            return false;
        }

        return [
            \Filament\Tables\Columns\TextColumn::make('has_javascript_powerup')
                ->getStateUsing(function (Slide $slide) {
                    return $slide->data['has_javascript_powerup'] ?? false;
                })
                ->formatStateUsing(function (bool $state) {
                    return $state ? 'Yes' : 'No';
                }),
        ];
    }
}
