<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Category;
use App\Observers\TransactionObserver;
use App\Observers\WalletObserver;
use App\Observers\CategoryObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Transaction::observe(TransactionObserver::class);
        Wallet::observe(WalletObserver::class);
        Category::observe(CategoryObserver::class);
    }
}
