<?php

namespace App\ShopItems;

use App\Models\ShopItem;
use App\Models\ShopItemUser;
use App\Models\Slide;
use Illuminate\Contracts\Database\Query\Builder;

#[ForShopItem('slide_invite_system')]
class SlideInviteSystem implements ShopItemInterface
{
    /**
     * Adds default time when a user purchases this item.
     */
    public static function onPurchase(ShopItem $shopItem, ShopItemUser $shopItemUser): void
    {
        $shopItemUser->data['slide_id'] = null;
    }

    /**
     * Returns html to be displayed in the inventory showing how much time is left
     */
    public static function showUserData(ShopItem $shopItem, ShopItemUser $shopItemUser): string
    {
        $usedOnSlide = $shopItemUser->data['slide_id'] ? Slide::find($shopItemUser->data['slide_id']) : null;
        $selectableSlides = auth()->user()->approvedSlides()->where(function(Builder $query) {
            $query->whereNull('data->invite_system_shop_item_user_id');
        })->get();

        return view('app.shop.items.slide-invite-system', compact('usedOnSlide', 'selectableSlides', 'shopItemUser'))->render();
    }

    /**
     * Sets the start time to now when a user uses this item.
     */
    public static function onUse(ShopItem $shopItem, ShopItemUser $shopItemUser, ...$arguments): null|\Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
    {
        // Guard against an invalid slide being passed
        $slide = $arguments[0];

        if (!$slide || !is_object($slide) || get_class($slide) !== Slide::class) {
            throw new \Exception('No slide passed to SlideInviteSystem::onUse! Please call a developer.');
        }

        // Check if the slide has already been used
        if ($slide->data && isset($slide->data['invite_system_shop_item_user_id'])) {
            return redirect()->back()->withErrors([
                'error' => 'This slide has already has an invite system!',
            ]);
        }

        $shopItemUser->data['slide_id'] = $slide->id;

        // TODO: Make this customizable:
        $shopItemUser->data['invite_system_title'] = $slide->title;
        $shopItemUser->data['invite_system_description'] = $slide->title;
        //$shopItemUser->data['invite_system_invitee_slots'] = null;
        $shopItemUser->data['invite_system_entry_fee_in_credits'] = 100;

        $slide->setData('invite_system_shop_item_user_id', $shopItemUser->id);

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
        return false;
    }
}
