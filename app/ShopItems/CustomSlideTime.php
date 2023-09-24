<?php

namespace App\ShopItems;

use App\Models\ShopItem;
use App\Models\ShopItemUser;

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

        return view('app.shop.items.custom-slide-time', compact('timeUsedInHours', 'timeTotalInHours'))->render();
    }
}
