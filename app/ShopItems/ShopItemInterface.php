<?php

namespace App\ShopItems;

use App\Models\ShopItem;
use App\Models\ShopItemUser;

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
}
