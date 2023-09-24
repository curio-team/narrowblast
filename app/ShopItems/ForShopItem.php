<?php

namespace App\ShopItems;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class ForShopItem{
    public function __construct(
        public string $uniqueId,
    ) {}
}
