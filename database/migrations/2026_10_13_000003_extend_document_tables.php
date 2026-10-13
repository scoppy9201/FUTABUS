<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_types', function (Blueprint $table) {
            // Loại giấy tờ có thời hạn không (nếu không thì không cần expiry_date)
            $table->boolean('has_expiry')->default(true)->after('is_required');
            // Chặn điều xe khi thiếu/hết hạn loại giấy tờ này
            $table->boolean('blocks_dispatch')->default(true)->after('has_expiry');
            // Số ngày cảnh báo trước hạn
            $table->unsignedSmallInteger('warning_days')->default(15)->after('blocks_dispatch');
        });

        Schema::table('vehicle_documents', function (Blueprint $table) {
            // Đường dẫn file đính kèm
            $table->string('file_path', 500)->nullable()->after('field_values');
            // Tên gốc của file
            $table->string('file_name', 255)->nullable()->after('file_path');
        });
    }

    public function down(): void
    {
        Schema::table('document_types', function (Blueprint $table) {
            $table->dropColumn(['has_expiry', 'blocks_dispatch', 'warning_days']);
        });

        Schema::table('vehicle_documents', function (Blueprint $table) {
            $table->dropColumn(['file_path', 'file_name']);
        });
    }
};
