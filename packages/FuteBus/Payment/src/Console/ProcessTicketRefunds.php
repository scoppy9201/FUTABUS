<?php

declare(strict_types=1);

namespace FuteBus\Payment\Console;

use FuteBus\Payment\Services\SePayRefundService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessTicketRefunds extends Command
{
    protected $signature = 'tickets:process-refunds';

    protected $description = 'Submit and reconcile queued SePay ticket refunds';

    public function handle(SePayRefundService $refunds): int
    {
        DB::table('ticket_refunds')->where('processing_mode', 'automatic')->whereIn('status', ['queued', 'pending'])
            ->orderBy('id')->chunkById(50, function ($rows) use ($refunds): void {
                foreach ($rows as $row) {
                    $refunds->process((int) $row->id);
                }
            });

        return self::SUCCESS;
    }
}
