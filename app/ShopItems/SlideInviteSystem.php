<?php

namespace App\ShopItems;

use App\Models\ShopItem;
use App\Models\ShopItemUser;

#[ForShopItem('slide_invite_system')]
class SlideInviteSystem implements ShopItemInterface
{
    /**
     * Adds default time when a user purchases this item.
     */
    public static function onPurchase(ShopItem $shopItem, ShopItemUser $shopItemUser): void
    {
        $shopItemUser->data['invite_system_id'] = null;
    }

    /**
     * Returns html to be displayed in the inventory showing how much time is left
     */
    public static function showUserData(ShopItem $shopItem, ShopItemUser $shopItemUser): string
    {
        $inviteSystemId = $shopItemUser->data['invite_system_id'];
        $inviteSystem = $inviteSystemId ? $shopItemUser->user->inviteSystems()->find($inviteSystemId) : null;

        return view('app.shop.items.slide-invite-system', compact('inviteSystem', 'shopItemUser'))->render();
    }

    /**
     * Sets the start time to now when a user uses this item.
     */
    public static function onUse(ShopItem $shopItem, ShopItemUser $shopItemUser, ...$arguments): null|\Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
    {
        // TODO
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
