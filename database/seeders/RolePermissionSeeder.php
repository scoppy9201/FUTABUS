<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Admin', 'slug' => 'admin'],
            ['name' => 'Nhà xe', 'slug' => 'bus-company'],
            ['name' => 'Nhân viên', 'slug' => 'staff'],
            ['name' => 'Khách hàng', 'slug' => 'customer'],
        ];

        $permissionGroups = config('permission_catalog', []);

        $roleIds = [];
        foreach ($roles as $r) {
            $roleId = DB::table('roles')->whereNull('bus_company_id')->where('slug', $r['slug'])->value('id');
            if ($roleId === null) {
                $roleId = DB::table('roles')->insertGetId($r + ['created_at' => now(), 'updated_at' => now()]);
            }
            $roleIds[$r['slug']] = $roleId;
        }

        $permIds = [];
        foreach ($permissionGroups as $group => $actions) {
            foreach ($actions as $action) {
                $slug = $group . '.' . $action;
                $permissionId = DB::table('permissions')->where('slug', $slug)->value('id');
                if ($permissionId === null) {
                    $permissionId = DB::table('permissions')->insertGetId([
                        'name' => ucfirst($group).' '.$action,
                        'slug' => $slug,
                        'group' => $group,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                $permIds[$slug] = $permissionId;
            }
        }

        // Admin: mọi quyền
        foreach ($permIds as $pid) {
            DB::table('permission_role')->insertOrIgnore([
                'permission_id' => $pid,
                'role_id'       => $roleIds['admin'],
            ]);
        }

        // Quản lý nhà xe: quyền nghiệp vụ và quản lý phân quyền trong phạm vi nhà xe.
        $business = [
            'bus_company.view', 'bus_company.update',
            'bus.view', 'bus.create', 'bus.update', 'bus.delete',
            'route.view', 'route.create', 'route.update', 'route.delete',
            'trip.view', 'trip.create', 'trip.update', 'trip.cancel',
            'seat_layout.view', 'seat_layout.create', 'seat_layout.update', 'seat_layout.delete',
            'booking.view', 'booking.create', 'booking.cancel',
            'payment.view', 'payment.create', 'payment.refund',
            'ticket.view', 'ticket.create', 'ticket.verify',
            'customer.view', 'customer.create', 'customer.update',
            'report.view',
            'user.view', 'user.create', 'user.update', 'user.delete',
            'role.view', 'role.create', 'role.update', 'role.delete',
            'dashboard.view',
        ];
        foreach ($business as $slug) {
            if (isset($permIds[$slug])) {
                DB::table('permission_role')->insertOrIgnore([
                    'permission_id' => $permIds[$slug],
                    'role_id'       => $roleIds['bus-company'],
                ]);
            }
        }

        // Khách hàng: chỉ đặt vé và xem
        $customer = [
            'trip.view', 'booking.view', 'booking.create', 'booking.cancel',
            'payment.view', 'payment.create', 'ticket.view',
        ];
        foreach ($customer as $slug) {
            if (isset($permIds[$slug])) {
                DB::table('permission_role')->insertOrIgnore([
                    'permission_id' => $permIds[$slug],
                    'role_id'       => $roleIds['customer'],
                ]);
            }
        }
    }
}
