<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $id = DB::table('permissions')->where('slug', 'ticket.exchange')->value('id');
        $id ??= DB::table('permissions')->insertGetId(['name' => 'Đổi vé miễn phí', 'slug' => 'ticket.exchange',
            'group'                                           => 'ticket', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        foreach (DB::table('roles')->whereIn('slug', ['admin', 'bus-company'])->pluck('id') as $role) {
            DB::table('permission_role')->insertOrIgnore(['role_id' => $role, 'permission_id' => $id]);
        }
    }

    public function down(): void
    {
        if (DB::table('ticket_exchanges')->where('status', 'completed')->exists()) {
            throw new RuntimeException('Preserve completed exchange records before removing exchange permissions.');
        }
        $id = DB::table('permissions')->where('slug', 'ticket.exchange')->value('id');
        if ($id) {
            DB::table('permission_role')->where('permission_id', $id)->delete();
            DB::table('permissions')->where('id', $id)->delete();
        }
    }
};
