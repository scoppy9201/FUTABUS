<?php

declare(strict_types=1);

namespace FuteBus\TripManagement\Console;

use FuteBus\TripManagement\Services\TripGenerator;
use Illuminate\Console\Command;

class GenerateTripsCommand extends Command
{
    protected $signature = 'trips:generate';
    protected $description = 'Sinh chuyến xe 30 ngày tới từ các lịch trình đang áp dụng';

    public function handle(TripGenerator $generator): int
    {
        $this->info('Đã sinh '.$generator->generateAll().' chuyến.');

        return self::SUCCESS;
    }
}