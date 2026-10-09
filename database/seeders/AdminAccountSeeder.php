<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use LogicException;

class AdminAccountSeeder extends Seeder
{
    public function run(): void
    {
        $email = trim((string) config('seed.admin.email'));
        $password = config('seed.admin.password');

        if (! is_string($password) || $password === '') {
            $this->command?->warn('Chưa đặt FUTABUS_ADMIN_PASSWORD; bỏ qua tài khoản admin.');

            return;
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($password) < 12) {
            throw new InvalidArgumentException('Email admin phải hợp lệ và mật khẩu phải có ít nhất 12 ký tự.');
        }

        DB::transaction(function () use ($email, $password): void {
            $roleId = DB::table('roles')->whereNull('bus_company_id')->where('slug', 'admin')->value('id');

            if ($roleId === null) {
                $roleId = DB::table('roles')->insertGetId([
                    'name' => 'Admin',
                    'slug' => 'admin',
                ]);
            }

            $admin = User::firstOrNew(['email' => $email]);

            if ($admin->exists && ! $admin->isAdmin()) {
                throw new LogicException('Email cấu hình đang thuộc tài khoản không phải admin.');
            }

            $admin->name = (string) config('seed.admin.name');
            $admin->email_verified_at ??= now();

            if (! $admin->exists || ! Hash::check($password, $admin->password)) {
                $admin->password = $password;
            }

            $admin->save();

            DB::table('role_user')->insertOrIgnore([
                'user_id' => $admin->id,
                'role_id' => $roleId,
            ]);

            foreach (DB::table('permissions')->pluck('id') as $permissionId) {
                DB::table('permission_role')->insertOrIgnore([
                    'permission_id' => $permissionId,
                    'role_id'       => $roleId,
                ]);
            }
        });

        $this->command?->info("Đã tạo/cập nhật tài khoản quản trị: {$email}");
    }
}
