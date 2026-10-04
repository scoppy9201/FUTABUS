<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $customerRoleId = DB::table('roles')->where('slug', 'customer')->value('id');

        for ($i = 0; $i < 20; $i++) {
            $u = User::factory()->create();
            DB::table('role_user')->insert([
                'user_id' => $u->id,
                'role_id' => $customerRoleId,
            ]);
            DB::table('customers')->insert([
                'user_id'    => $u->id,
                'full_name'  => $u->name,
                'email'      => $u->email,
                'phone'      => $u->phone,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
