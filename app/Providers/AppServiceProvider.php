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
        $this->overrideMailConfig();
    }

    private function overrideMailConfig(): void
    {
        try {
            if (!\Schema::hasTable('email_settings')) return;

            $setting = \App\Models\EmailSetting::where('is_active', true)->first();
            if (!$setting) return;

            \Config::set('mail.default', 'smtp');
            \Config::set('mail.mailers.smtp', [
                'transport'  => 'smtp',
                'host'       => $s->mail_host,
                'port'       => $s->mail_port,
                'username'   => $s->mail_username,
                'password'   => $s->mail_password,
                'encryption' => $s->mail_encryption,
            ]);
            \Config::set('mail.from.address', $setting->mail_from_address);
            \Config::set('mail.from.name',    $setting->mail_from_name);

        } catch (\Exception $e) {
            \Log::error('Lỗi khi override mail config: ' . $e->getMessage());
        }
    }
}
