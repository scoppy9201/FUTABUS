<?php

namespace App\Console\Commands;

use App\Models\Budgets;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpireOutdatedBudgets extends Command
{
    protected $signature   = 'budgets:expire';
    protected $description = 'Tự động khóa các ngân sách đã hết hạn';

    public function handle()
    {
        $today = now()->startOfDay();
        $count = 0;

        $this->info("Đang kiểm tra ngân sách hết hạn...");

        Budgets::where('trang_thai', true)
            ->where('da_het_han', false)
            ->whereNotNull('ngay_ket_thuc')
            ->where('ngay_ket_thuc', '<', $today)
            ->chunk(100, function ($budgets) use (&$count) {
                foreach ($budgets as $budget) {
                    DB::beginTransaction();
                    try {
                        $budget->update([
                            'trang_thai' => false,
                            'da_het_han' => true,
                        ]);

                        Log::info("Budget hết hạn: [{$budget->id}] {$budget->ten_ngan_sach} - User: {$budget->user_id}");
                        $count++;

                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error("Lỗi expire budget [{$budget->id}]: " . $e->getMessage());
                    }
                }
            });

        $this->info("Đã khóa {$count} ngân sách hết hạn!");

        // Xử lý reset ngân sách theo tháng
        $resetCount = 0;

        Budgets::where('trang_thai', true)
            ->where('da_het_han', false)
            ->where('loai_thoi_gian', 'thang')
            ->where('tu_dong_reset', true)
            ->whereNotNull('ngay_ket_thuc')
            ->where('ngay_ket_thuc', '<', $today)
            ->chunk(100, function ($budgets) use (&$resetCount) {
                foreach ($budgets as $budget) {
                    DB::beginTransaction();
                    try {
                        // Reset số dư + cập nhật sang tháng mới
                        $newStart = now()->startOfMonth()->toDateString();
                        $newEnd   = now()->endOfMonth()->toDateString();

                        $budget->update([
                            'so_du'        => $budget->ngan_sach_goc,
                            'ngay_bat_dau' => $newStart,
                            'ngay_ket_thuc'=> $newEnd,
                            'trang_thai'   => true,
                            'da_het_han'   => false,
                        ]);

                        Log::info("Budget reset tháng mới: [{$budget->id}] {$budget->ten_ngan_sach}");
                        $resetCount++;

                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error("Lỗi reset budget [{$budget->id}]: " . $e->getMessage());
                    }
                }
            });

        $this->info("Đã reset {$resetCount} ngân sách sang tháng mới!");

        return 0;
    }
}