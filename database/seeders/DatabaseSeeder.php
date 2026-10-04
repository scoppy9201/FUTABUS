<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);
        $this->call(AdminAccountSeeder::class);
        $this->call(BusCompanySeeder::class);
        $this->call(DemoDataSeeder::class);
        $this->call(PromotionSeeder::class);
        $this->call(NewsArticleSeeder::class);
        $this->call(FaqSeeder::class);
        $this->call(BranchOfficeSeeder::class);
        $this->call(ScheduleRouteSeeder::class);
    }
}
