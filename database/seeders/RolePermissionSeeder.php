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

        $permissionGroups = [
            'bus_company' => ['view', 'create', 'update', 'delete'],
            'bus'         => ['view', 'create', 'update', 'delete'],
            'route'       => ['view', 'create', 'update', 'delete'],
            'trip'        => ['view', 'create', 'update', 'delete', 'cancel'],
            'seat_layout' => ['view', 'create', 'update', 'delete'],
            'booking'     => ['view', 'create', 'update', 'cancel'],
            'payment'     => ['view', 'create', 'refund'],
            'ticket'      => ['view', 'create', 'verify'],
            'customer'    => ['view', 'create', 'update', 'delete'],
            'report'      => ['view'],
            'user'        => ['view', 'create', 'update', 'delete'],
            'setting'     => ['view', 'update'],
        ];

        $roleIds = [];
        foreach ($roles as $r) {
            $roleIds[$r['slug']] = DB::table('roles')->insertGetId($r);
        }

        $permIds = [];
        foreach ($permissionGroups as $group => $actions) {
            foreach ($actions as $action) {
                $slug = $group . '.' . $action;
                $permIds[$slug] = DB::table('permissions')->insertGetId([
                    'name'  => ucfirst($group) . ' ' . $action,
                    'slug'  => $slug,
                    'group' => $group,
                ]);
            }
        }

        // Admin: mọi quyền
        foreach ($permIds as $pid) {
            DB::table('permission_role')->insert([
                'permission_id' => $pid,
                'role_id'       => $roleIds['admin'],
            ]);
        }

        // Nhà xe + Nhân viên: quyền nghiệp vụ
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
        ];
        foreach ($business as $slug) {
            if (isset($permIds[$slug])) {
                DB::table('permission_role')->insert([
                    'permission_id' => $permIds[$slug],
                    'role_id'       => $roleIds['bus-company'],
                ]);
                DB::table('permission_role')->insert([
                    'permission_id' => $permIds[$slug],
                    'role_id'       => $roleIds['staff'],
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
                DB::table('permission_role')->insert([
                    'permission_id' => $permIds[$slug],
                    'role_id'       => $roleIds['customer'],
                ]);
            }
        }
    }
}