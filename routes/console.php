<?php

use Illuminate\Support\Facades\Schedule;

// Chạy mỗi ngày lúc 00:05 sáng
Schedule::command('budgets:expire')->dailyAt('00:05');