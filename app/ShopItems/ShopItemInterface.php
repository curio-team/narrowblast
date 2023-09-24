<?php

namespace App\ShopItems;

use App\Models\ShopItem;
use App\Models\ShopItemUser;
use App\Models\Slide;

interface ShopItemInterface
{
    /**
     * Called when a user purchases this item.
     *
     * Use this to setup any data that is required for the item. For example:
     * - If it is a consumable, add the amount of uses to the user
     * - If it is time restricted, add the time to the user
     *
     * The ShopItemUser will be saved after this method is called, so no need to save it yourself.
     */
    public static function onPurchase(ShopItem $shopItem, ShopItemUser $shopItemUser): void;

    /**
     * Must return html to be displayed in the inventory.
     *
     * Shows current data state of the item, for example:
     * - If it is a consumable, show how many uses are left
     * - If it is a pet, show the pet's name
     */
    public static function showUserData(ShopItem $shopItem, ShopItemUser $shopItemUser): string;

    /**
     * Called when a user uses this item.
     *
     * Use this to update any data that is required for the item. For example:
     * - If it is a consumable, deduct the amount of uses from the user
     * - If it is time restricted, set the start time to now (so we can calculate how much time is left later)
     *
     * The ShopItemUser will be saved when this method returns null, so no need to save it yourself.
     * Return a redirect response to redirect the user back with validation errors, e.g:
     *      return redirect()->back()->withErrors([...]);
     */
    public static function onUse(ShopItem $shopItem, ShopItemUser $shopItemUser, ...$arguments): null|\Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse;

    /**
     * Called periodically while the product is being used.
     *
     * For time restricted items this can be used to check if the item has expired.
     * Return false to deactivate the item.
     *
     * The ShopItemUser will NOT be saved after this method is called, so you must save it yourself.
     */
    public static function onTick(ShopItem $shopItem, ShopItemUser $shopItemUser, ...$arguments): bool;

    /**
     * Called to get custom slide column for the slides table
     */
    public static function getCustomSlideColumns(ShopItem $shopItem, ShopItemUser $shopItemUser, bool $isApproved): false|array;
}
