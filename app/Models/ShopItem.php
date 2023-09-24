<?php

namespace App\Models;

use App\ShopItems\ForShopItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopItem extends Model
{
    use HasFactory;

    const SHOP_ITEMS_APP_PATH = 'ShopItems';

    const STORAGE_DISK = 'public';
    const FILE_DIRECTORY = 'shop_items';

    protected $fillable = [
        'name',
        'unique_id',
        'description',
        'image_path',
        'cost_in_credits',
        'max_per_user',
    ];

    public function userHasMaximum(User $user)
    {
        return $this->max_per_user !== null
            && $this->shopItemUsers()->where('user_id', auth()->id())->count() >= $this->max_per_user;
    }

    /**
     * Returns the ShopItem class that matches the unique_id
     */
    private function getShopItemClass()
    {
        $shopItemClassFiles = glob(app_path(self::SHOP_ITEMS_APP_PATH . DIRECTORY_SEPARATOR . '*.php'));
        foreach ($shopItemClassFiles as $shopItemClassFile) {
            require_once $shopItemClassFile;
            $shopItemClass = 'App\\' . self::SHOP_ITEMS_APP_PATH . '\\' . substr(basename($shopItemClassFile), 0, -4);

            try {
                $reflector = new \ReflectionClass($shopItemClass);
                $attributes = $reflector->getAttributes(ForShopItem::class);
            } catch (\ReflectionException $e) {
                continue;
            }

            if (count($attributes) === 0) {
                continue;
            }

            if (!in_array('App\\ShopItems\\ShopItemInterface', class_implements($shopItemClass))) {
                throw new \Exception('Shop item class ' . $shopItemClass . ' does not implement ShopItemInterface! Please call a developer.');
            }

            foreach ($attributes as $attribute) {
                foreach ($attribute->getArguments() as $argument) {
                    if ($argument === $this->unique_id) {
                        return $shopItemClass;
                    }
                }
            }
        }

        throw new \Exception('No shop item class found for unique_id: ' . $this->unique_id . '! Please call a developer.');
    }

    /**
     * Calls the given method on the shop item class that matches the unique_id
     */
    function callShopItemMethod(string $method, ShopItemUser $shopItemUser, ...$arguments)
    {
        $shopItemClass = $this->getShopItemClass();
        return call_user_func([$shopItemClass, $method], $this, $shopItemUser, ...$arguments);
    }

    /**
     * Purchases an item for the specified user.
     * Does not deduct credits, this should be done before calling this method and in a transaction with this method.
     */
    function purchaseFor(User $user): void
    {
        $shopItemUser = new ShopItemUser();
        $shopItemUser->user_id = $user->id;
        $shopItemUser->shop_item_id = $this->id;
        $shopItemUser->cost_in_credits = $this->cost_in_credits;
        $shopItemUser->data = [];

        $this->callShopItemMethod('onPurchase', $shopItemUser);

        $shopItemUser->save();
    }

    /**
     * Shows custom data based on the item it is, matches an item using the unique_id
     */
    function showUserData(ShopItemUser $shopItemUser)
    {
        return $this->callShopItemMethod('showUserData', $shopItemUser);
    }

    /**
     *
     * Relationships
     *
     */

    /**
     * The pivot for this item and the user that owns it
     */
    public function shopItemUsers() {
        return $this->hasMany(ShopItemUser::class);
    }
}
