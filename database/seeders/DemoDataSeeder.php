<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {

        $admin = User::factory()->admin()->create();
        $staff = User::factory()->staff()->create();
        $customer = User::factory()->customer()->create();

        $roles = DB::table('roles')->pluck('id', 'slug');
        DB::table('role_user')->insert([
            ['user_id' => $admin->id, 'role_id' => $roles['admin']],
            ['user_id' => $staff->id, 'role_id' => $roles['staff']],
            ['user_id' => $customer->id, 'role_id' => $roles['customer']],
        ]);


        foreach (range(1, 20) as $i) {
            $u = User::factory()->create();
            DB::table('role_user')->insert([
                'user_id' => $u->id,
                'role_id' => $roles['customer'],
            ]);
            DB::table('customers')->insert([
                'user_id'  => $u->id,
                'full_name'=> $u->name,
                'email'    => $u->email,
                'phone'    => $u->phone,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}