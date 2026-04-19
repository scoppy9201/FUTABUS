<?php

namespace App\Console\Commands;

use App\Models\Budgets;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillTransactionWalletId extends Command
{
    protected $signature   = 'backfill:transaction-wallet-id';
    protected $description = 'Gắn wallet_id cho các giao dịch cũ chưa có wallet_id';

    public function handle()
    {
        $total   = Transaction::whereNull('wallet_id')->count();
        $updated = 0;
        $skipped = 0;

        $this->info("Tổng giao dịch chưa có wallet_id: {$total}");

        if ($total === 0) {
            $this->info('Không có giao dịch nào cần backfill!');
            return 0;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        Transaction::whereNull('wallet_id')
            ->chunk(200, function ($transactions) use (&$updated, &$skipped, $bar) {
                foreach ($transactions as $tx) {
                    // Tìm budget phù hợp nhất:
                    // Cùng user + cùng category + được tạo trước hoặc cùng ngày giao dịch
                    // Ưu tiên budget được tạo GẦN NHẤT trước thời điểm giao dịch
                    $wallet = Budgets::where('user_id', $tx->user_id)
                        ->where('category_id', $tx->category_id)
                        ->where('created_at', '<=', $tx->created_at)
                        ->orderBy('created_at', 'desc') // lấy budget gần nhất
                        ->first();

                    if ($wallet) {
                        $tx->update(['wallet_id' => $wallet->id]);
                        $updated++;
                    } else {
                        // Không tìm được budget phù hợp → để null
                        $skipped++;
                    }

                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine();
        $this->info("Đã gắn wallet_id: {$updated} giao dịch");
        $this->info("Bỏ qua (không có budget): {$skipped} giao dịch");

        return 0;
    }
}