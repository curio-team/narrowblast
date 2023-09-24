<?php

namespace Database\Seeders;

use App\Models\Screen;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Seed the Narrowcasting screen at the front door
        Screen::create([
            'name' => 'NarrowBlast Voordeur',
        ]);

        $this->call(ShopItemSeeder::class);
    }
}
