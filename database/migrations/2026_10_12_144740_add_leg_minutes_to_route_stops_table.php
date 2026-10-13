<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('route_stops', function (Blueprint $t) {
            $t->unsignedSmallInteger('leg_minutes')->default(0)->after('stop_order');
        });

        DB::table('route_stops')->where('status', 'inactive')->delete();

        // Chuyển dữ liệu cũ: leg = offset - offset điểm trước (điểm đầu = offset)
        foreach (DB::table('route_stops')->distinct()->pluck('route_id') as $routeId) {
            $prev = 0;
            $order = 1;
            foreach (DB::table('route_stops')->where('route_id', $routeId)
                ->orderBy('offset_minutes')->orderBy('id')->get() as $s) {
                DB::table('route_stops')->where('id', $s->id)->update([
                    'leg_minutes' => max(0, $s->offset_minutes - $prev),
                    'stop_order'  => $order++,
                ]);
                $prev = $s->offset_minutes;
            }
        }
    }

    public function down(): void
    {
        Schema::table('route_stops', function (Blueprint $t) {
            $t->dropColumn('leg_minutes');
        });
    }
};
