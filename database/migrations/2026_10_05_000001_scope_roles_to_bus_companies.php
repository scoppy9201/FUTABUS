<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table): void {
            $table->dropUnique('roles_name_unique');
            $table->dropUnique('roles_slug_unique');
            $table->foreignId('bus_company_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->unique(['bus_company_id', 'name'], 'roles_company_name_unique');
            $table->unique(['bus_company_id', 'slug'], 'roles_company_slug_unique');
        });

        $catalog = [
            'dashboard.view' => ['Dashboard access', 'dashboard'],
            'role.view' => ['View access roles', 'role'],
            'role.create' => ['Create access roles', 'role'],
            'role.update' => ['Update access roles', 'role'],
            'role.delete' => ['Delete access roles', 'role'],
        ];

        foreach ($catalog as $slug => [$name, $group]) {
            DB::table('permissions')->insertOrIgnore([
                'name' => $name,
                'slug' => $slug,
                'group' => $group,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $adminRoleId = DB::table('roles')->whereNull('bus_company_id')->where('slug', 'admin')->value('id');
        $managerRoleId = DB::table('roles')->whereNull('bus_company_id')->where('slug', 'bus-company')->value('id');
        $permissionIds = DB::table('permissions')->whereIn('slug', array_keys($catalog))
            ->pluck('id', 'slug');

        foreach ([$adminRoleId, $managerRoleId] as $roleId) {
            if ($roleId === null) {
                continue;
            }

            $rolePermissionIds = $permissionIds;
            if ($roleId === $managerRoleId) {
                $managerSlugs = [
                    ...array_keys($catalog),
                    'bus_company.view', 'bus_company.update',
                    'bus.view', 'bus.create', 'bus.update', 'bus.delete',
                    'route.view', 'route.create', 'route.update', 'route.delete',
                    'trip.view', 'trip.create', 'trip.update', 'trip.cancel',
                    'seat_layout.view', 'seat_layout.create', 'seat_layout.update', 'seat_layout.delete',
                    'booking.view', 'booking.create', 'booking.cancel',
                    'payment.view', 'payment.create', 'payment.refund',
                    'ticket.view', 'ticket.create', 'ticket.verify',
                    'customer.view', 'customer.create', 'customer.update',
                    'report.view', 'user.view', 'user.create', 'user.update', 'user.delete',
                ];
                $rolePermissionIds = DB::table('permissions')
                    ->whereIn('slug', $managerSlugs)
                    ->pluck('id', 'slug');
            }

            foreach ($rolePermissionIds as $permissionId) {
                DB::table('permission_role')->insertOrIgnore([
                    'permission_id' => $permissionId,
                    'role_id' => $roleId,
                ]);
            }
        }

    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table): void {
            $table->dropForeign(['bus_company_id']);
            $table->dropUnique('roles_company_name_unique');
            $table->dropUnique('roles_company_slug_unique');
            $table->dropColumn('bus_company_id');
            $table->unique('name');
            $table->unique('slug');
        });

        DB::table('permissions')->whereIn('slug', [
            'dashboard.view', 'role.view', 'role.create', 'role.update', 'role.delete',
        ])->delete();
    }
};
