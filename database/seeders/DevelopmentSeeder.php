<?php

namespace Database\Seeders;

use App\Models\Slide;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Seeder;
use Illuminate\Http\File;

class DevelopmentSeeder extends Seeder
{
    const TEST_SLIDES_PATH = '../../tests/TestData/slides/';
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Makes development easier if I just add myself to the database
        User::create([
            'id' => 'tl10',
            'name' => 'Tim',
            'email' => 'tl10@curio.nl',
            'type' => 'teacher',
            'credits' => 10000,
        ]);

        // Seed some of the test slides for the first user (tl10)
        $testSlides = [
            [
                'title' => 'Test Slide 1',
                'path' => 'slide-with-js.html',
                'finalized_at' => now(),
                'approved_at' => now(),
                'approver_id' => 'tl10',
            ],
            [
                'title' => 'Test Slide with Betting',
                'path' => 'slide-with-js-and-betting.html',
            ],
        ];

        foreach ($testSlides as $testSlide) {
            $testSlide['path'] = Storage::disk(Slide::STORAGE_DISK)
                ->putFile(
                    Slide::FILE_DIRECTORY,
                    new File(realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . self::TEST_SLIDES_PATH . DIRECTORY_SEPARATOR . $testSlide['path'])
                );

            $testSlide['user_id'] = 'tl10';

            Slide::create($testSlide);
        }
    }
}
