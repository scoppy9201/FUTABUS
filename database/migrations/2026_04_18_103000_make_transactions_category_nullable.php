<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'category_id')) {

                // Drop foreign key (nếu tồn tại)
                try {
                    $table->dropForeign(['category_id']);
                } catch (\Exception $e) {
                    // Nếu chưa có foreign key thì bỏ qua
                }

                // Sửa column thành nullable
                $table->unsignedBigInteger('category_id')->nullable()->change();

                // Thêm lại foreign key với set null
                $table->foreign('category_id')
                    ->references('id')
                    ->on('categories')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'category_id')) {

                try {
                    $table->dropForeign(['category_id']);
                } catch (\Exception $e) {
                    // bỏ qua nếu chưa có
                }

                // Trả về NOT NULL
                $table->unsignedBigInteger('category_id')->nullable(false)->change();

                // FK về cascade
                $table->foreign('category_id')
                    ->references('id')
                    ->on('categories')
                    ->cascadeOnDelete();
            }
        });
    }
};
