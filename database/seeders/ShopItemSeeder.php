<?php

namespace Database\Seeders;

use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShopItemSeeder extends Seeder
{
    const IMAGE_PATH = './shop_items/';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Creates the default shop items
        $shopItems = [
            [
                'name' => 'Custom Slide (14 dagen)',
                'description' => 'Presenteer 14 dagen lang een eigen gemaakte \'slide\' op NarrowBlast! Gebruik HTML en CSS om een webpagina te maken en upload die naar ons platform. Iedereen die langs de NarrowBlast loopt zal jouw slide zien. Jouw slide wordt eerst gecontroleerd alvorens deze wordt getoond.',
                'image_path' => 'presentation.png',
                'unique_id' => 'slide_14d',
                'cost_in_credits' => 100,
            ],
            [
                'name' => 'Custom Slide (7 dagen)',
                'description' => 'Presenteer 7 dagen lang een eigen gemaakte \'slide\' op NarrowBlast! Gebruik HTML en CSS om een webpagina te maken en upload die naar ons platform. Iedereen die langs de NarrowBlast loopt zal jouw slide zien. Jouw slide wordt eerst gecontroleerd alvorens deze wordt getoond.',
                'image_path' => 'presentation.png',
                'unique_id' => 'slide_7d',
                'cost_in_credits' => 75,
            ],
            [
                'name' => 'Javascript Slide Power-up',
                'description' => 'Kies één slide die je hebt gemaakt en activeer er Javascript voor. Waar normaal gesproken de slides alleen HTML en CSS ondersteunen, wordt op de slide naar jouw keuze ook Javascript geactiveerd, zodat je deze interactief kunt maken. Dit werkt voor maar één slide, daarna moet je deze power-up opnieuw kopen.',
                'image_path' => 'animated.png',
                'unique_id' => 'slide_powerup_js',
                'cost_in_credits' => 50,
            ],
        ];

        foreach ($shopItems as $shopItem) {
            // Copy the image
              $shopItem['image_path'] = Storage::disk(\App\Models\ShopItem::STORAGE_DISK)
                    ->putFile(
                        \App\Models\ShopItem::FILE_DIRECTORY,
                        new File(realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . self::IMAGE_PATH . DIRECTORY_SEPARATOR . $shopItem['image_path'])
                    );

            \App\Models\ShopItem::create($shopItem);
        }
    }
}
