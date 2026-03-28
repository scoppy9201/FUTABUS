<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('money_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('ten_vi', 100);
            $table->enum('loai_vi', [
                'tien_mat',
                'ngan_hang',
                'vi_dien_tu',
                'the_tin_dung',
                'dau_tu',
                'khac',
            ])->default('tien_mat');

            $table->decimal('so_du', 18, 2)->default(0);
            $table->decimal('so_du_ban_dau', 18, 2)->default(0);

            $table->string('don_vi_tien_te', 10)->default('VND');
            $table->string('bieu_tuong', 50)->default('💰');   // emoji hoặc tên icon
            $table->text('mo_ta')->nullable();

            // active = đang dùng, inactive = ẩn, archived = lưu trữ
            $table->enum('trang_thai', ['active', 'inactive', 'archived'])->default('active');

            $table->timestamps();

            $table->index(['user_id', 'trang_thai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('money_wallets');
    }
};
