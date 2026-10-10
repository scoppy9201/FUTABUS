<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class FeaturedContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            FeaturedArticleSeeder::class,
            FeaturedArticleBodySeeder::class,
        ]);
    }
}
