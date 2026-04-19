<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\BudgetsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AIAssistantController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\SplitGroupController;
use App\Http\Controllers\GroupMemberController;
use App\Http\Controllers\GroupBalanceController;
use App\Http\Controllers\GroupExpenseController;
use App\Http\Controllers\GroupDebtController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\EmailSettingController;
use App\Http\Controllers\Api\MoneyWalletController;
use App\Http\Controllers\Api\WalletTransferController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Api\QrTransferController;

/*
|--------------------------------------------------------------------------
| API Routes
|
| Tất cả routes đều có prefix /api/v1
| VD: /api/v1/auth/login, /api/v1/groups/...
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->name('api.')->group(function () {

    /*
    | AUTH ROUTES (public — không cần token)
    */
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('/register', [RegisterController::class, 'register'])->name('register');
        Route::post('/login',    [LoginController::class,    'login'])   ->name('login');

        Route::prefix('password')->name('password.')->group(function () {
            Route::post('/forgot', [ForgotPasswordController::class, 'sendResetCode'])->name('forgot');
            Route::post('/verify', [ForgotPasswordController::class, 'verifyCode'])   ->name('verify');
            Route::post('/reset',  [ForgotPasswordController::class, 'resetPassword'])->name('reset');
        });
    });

    /*
    | PUBLIC INVITATION ROUTES (không cần token — link gửi qua email)
    */
    Route::prefix('groups')->name('groups.')->group(function () {
        Route::get('/invitations/{token}/accept',  [GroupMemberController::class, 'accept']) ->name('invite.accept');
        Route::get('/invitations/{token}/decline', [GroupMemberController::class, 'decline'])->name('invite.decline');
    });

    /*
    | AUTHENTICATED ROUTES (cần Sanctum token)
    */
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/auth/logout', [LoginController::class, 'logout'])->name('auth.logout');

        /*
        | DASHBOARD
        */
        Route::prefix('dashboard')->name('dashboard.')->group(function () {
            Route::get('/',             [DashboardController::class, 'index'])     ->name('index');
            Route::get('/export',       [DashboardController::class, 'export'])    ->name('export');
            Route::post('/export-pdf', [DashboardController::class, 'exportPdf'])->name('export-pdf');
            Route::post('/send-report', [DashboardController::class, 'sendReport'])->name('send-report'); 
        });

        /*
        | SEARCH
        */
        Route::get('/search', [SearchController::class, 'index'])->name('search');

        /*
        | CURRENCY
        */
        Route::prefix('currency')->name('currency.')->group(function () {
            Route::get('/',                              [CurrencyController::class, 'index'])        ->name('index');
            Route::post('/convert',                      [CurrencyController::class, 'convert'])      ->name('convert');
            Route::get('/history',                       [CurrencyController::class, 'history'])      ->name('history');
            Route::delete('/history',                    [CurrencyController::class, 'clearHistory']) ->name('history.clear');
            Route::delete('/history/{currencyHistory}',  [CurrencyController::class, 'deleteHistory'])->name('history.delete');
        });

        /*
        | PROFILE
        */
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/',          [ProfileController::class, 'show'])        ->name('show');
            Route::patch('/',        [ProfileController::class, 'update'])      ->name('update');
            Route::post('/avatar',   [ProfileController::class, 'updateAvatar'])->name('avatar.update');
            Route::delete('/avatar', [ProfileController::class, 'deleteAvatar'])->name('avatar.delete');
        });

        /*
        | CHANGE PASSWORD
        */
        Route::post('/password/change', [ChangePasswordController::class, 'changePassword'])->name('password.change');

        /*
        | SETTINGS
        */
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingsController::class, 'index'])->name('index');

            Route::prefix('email')->name('email.')->group(function () {
                Route::get('/',         [EmailSettingController::class, 'show'])    ->name('show');
                Route::patch('/',       [EmailSettingController::class, 'update'])  ->name('update');
                Route::patch('/toggle', [EmailSettingController::class, 'toggle'])  ->name('toggle');
                Route::post('/test',    [EmailSettingController::class, 'testMail'])->name('test');
            });
        });

        /*
        | CATEGORIES
        */
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/',                    [CategoryController::class, 'index'])        ->name('index');
            Route::post('/',                   [CategoryController::class, 'store'])        ->name('store');
            Route::get('/{category}',          [CategoryController::class, 'show'])         ->name('show');
            Route::patch('/{category}',        [CategoryController::class, 'update'])       ->name('update');
            Route::delete('/{category}',       [CategoryController::class, 'destroy'])      ->name('destroy');
            Route::patch('/{category}/status', [CategoryController::class, 'toggleStatus']) ->name('toggle-status');
        });

        /*
        | BUDGETS
        */
        Route::prefix('budgets')->name('budgets.')->group(function () {
            Route::get('/',                  [BudgetsController::class, 'index'])       ->name('index');
            Route::post('/',                 [BudgetsController::class, 'store'])       ->name('store');
            Route::get('/{wallet}',          [BudgetsController::class, 'show'])        ->name('show');
            Route::patch('/{wallet}',        [BudgetsController::class, 'update'])      ->name('update');
            Route::delete('/{wallet}',       [BudgetsController::class, 'destroy'])     ->name('destroy');
            Route::patch('/{wallet}/status', [BudgetsController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/{wallet}/sync',    [BudgetsController::class, 'syncBalance']) ->name('sync-balance');
        });

        /*
        | TRANSACTIONS
        */
        Route::apiResource('transactions', TransactionController::class);

        /*
        | GROUPS (Split bill)
        */
        Route::prefix('groups')->name('groups.')->group(function () {

            Route::get('/search-users', [SplitGroupController::class, 'searchUsers'])->name('search-users');
            Route::get('/',             [SplitGroupController::class, 'index'])      ->name('index');
            Route::post('/',            [SplitGroupController::class, 'store'])      ->name('store');
            Route::get('/{group}',      [SplitGroupController::class, 'show'])       ->name('show');
            Route::patch('/{group}',    [SplitGroupController::class, 'update'])     ->name('update');
            Route::delete('/{group}',   [SplitGroupController::class, 'destroy'])    ->name('destroy');
            Route::patch('/{group}/balance-visibility', [SplitGroupController::class, 'toggleBalanceVisibility'])->name('toggle-visibility');

            // Members
            Route::post('/{group}/members',                [GroupMemberController::class, 'invite']) ->name('members.invite');
            Route::delete('/{group}/members/leave',        [GroupMemberController::class, 'leave'])  ->name('members.leave');
            Route::delete('/{group}/members/{member}',     [GroupMemberController::class, 'remove']) ->name('members.remove');
            Route::patch('/{group}/members/{member}/role', [GroupMemberController::class, 'promote'])->name('members.promote');

            // Balance & Proposals
            Route::get('/{group}/balance',                                [GroupBalanceController::class, 'index'])  ->name('balance.index');
            Route::post('/{group}/balance/proposals',                     [GroupBalanceController::class, 'propose'])->name('balance.propose');
            Route::patch('/{group}/balance/proposals/{proposal}/approve', [GroupBalanceController::class, 'approve'])->name('balance.approve');
            Route::patch('/{group}/balance/proposals/{proposal}/reject',  [GroupBalanceController::class, 'reject']) ->name('balance.reject');
            Route::patch('/{group}/balance/proposals/{proposal}/cancel',  [GroupBalanceController::class, 'cancel']) ->name('balance.cancel');

            // Expenses
            Route::get('/{group}/expenses',                                [GroupExpenseController::class, 'index'])  ->name('expense.index');
            Route::post('/{group}/expenses',                               [GroupExpenseController::class, 'store'])  ->name('expense.store');
            Route::patch('/{group}/expenses/proposals/{proposal}/approve', [GroupExpenseController::class, 'approve'])->name('expense.approve');
            Route::patch('/{group}/expenses/proposals/{proposal}/reject',  [GroupExpenseController::class, 'reject']) ->name('expense.reject');
            Route::patch('/{group}/expenses/proposals/{proposal}/cancel',  [GroupExpenseController::class, 'cancel']) ->name('expense.cancel');

            // Debts
            Route::post('/{group}/debts',                [GroupDebtController::class, 'store'])  ->name('debt.store');
            Route::get('/{group}/debts/summary',         [GroupDebtController::class, 'summary'])->name('debt.summary');
            Route::patch('/{group}/debts/{debt}/settle', [GroupDebtController::class, 'settle']) ->name('debt.settle');
        });

        /*
        | NOTIFICATIONS
        */
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/',                        [NotificationController::class, 'index'])             ->name('index');
            Route::get('/dropdown',                [NotificationController::class, 'dropdown'])          ->name('dropdown');
            Route::get('/by-date',                 [NotificationController::class, 'byDate'])            ->name('by-date');
            Route::get('/badge',                   [NotificationController::class, 'badge'])             ->name('badge');
            Route::patch('/read-all',              [NotificationController::class, 'markAllRead'])       ->name('mark-all-read');
            Route::patch('/{notification}/read',   [NotificationController::class, 'markRead'])          ->name('mark-read');
            Route::post('/invite-action/{token}',  [NotificationController::class, 'handleInviteAction'])->name('invite-action');
        });

        /*
        | MONEY WALLETS
        */
        Route::prefix('money-wallets')->name('money-wallets.')->group(function () {

            Route::get('/',         [MoneyWalletController::class, 'index'])->name('index');
            Route::post('/',        [MoneyWalletController::class, 'store'])->name('store');
            Route::get('/summary',  [MoneyWalletController::class, 'summary'])->name('summary');

            // QR 
            Route::get('/qr/history',                [QrTransferController::class, 'history']) ->name('qr.history');
            Route::post('/qr/generate',              [QrTransferController::class, 'generate'])->name('qr.generate');
            Route::get('/qr/{token}',                [QrTransferController::class, 'show'])    ->name('qr.show');
            Route::post('/qr/{token}/confirm',       [QrTransferController::class, 'confirm']) ->name('qr.confirm');
            Route::post('/qr/{qrTransfer}/cancel',   [QrTransferController::class, 'cancel'])  ->name('qr.cancel');

            // Wallet CRUD & sub-resources
            Route::get('/{moneyWallet}',                    [MoneyWalletController::class, 'show'])        ->name('show');
            Route::match(['PUT','PATCH'], '/{moneyWallet}', [MoneyWalletController::class, 'update'])      ->name('update');
            Route::delete('/{moneyWallet}',                 [MoneyWalletController::class, 'destroy'])     ->name('destroy');
            Route::post('/{moneyWallet}/restore',           [MoneyWalletController::class, 'restore'])     ->name('restore');
            Route::post('/{moneyWallet}/adjust',            [MoneyWalletController::class, 'adjust'])      ->name('adjust');
            Route::patch('/{moneyWallet}/balance',          [MoneyWalletController::class, 'adjust'])      ->name('balance');
            Route::get('/{moneyWallet}/transactions',       [MoneyWalletController::class, 'transactions'])->name('transactions');
            Route::get('/{moneyWallet}/transfers',          [MoneyWalletController::class, 'transfers'])   ->name('transfers');
            Route::get('/{moneyWallet}/adjustments',        [MoneyWalletController::class, 'adjustments']) ->name('adjustments');
        });

        /*
        | WALLET TRANSFERS
        */
        Route::prefix('wallet-transfers')->name('wallet-transfers.')->group(function () {
            Route::get('/',                    [WalletTransferController::class, 'index'])  ->name('index');
            Route::post('/',                   [WalletTransferController::class, 'store'])  ->name('store');
            Route::delete('/{walletTransfer}', [WalletTransferController::class, 'destroy'])->name('destroy');
        });

        /*
        | AI ASSISTANT
        */
        Route::prefix('ai')->name('ai.')->group(function () {
            Route::get('/suggestions',        [AIAssistantController::class, 'suggestions'])      ->name('suggestions');
            Route::get('/insights',           [AIAssistantController::class, 'insights'])         ->name('insights');
            Route::post('/chat',              [AIAssistantController::class, 'chat'])             ->name('chat');
            Route::post('/analyze',           [AIAssistantController::class, 'analyze'])          ->name('analyze');
            Route::post('/forecast',          [AIAssistantController::class, 'forecast'])         ->name('forecast');
            Route::post('/budget-suggestion', [AIAssistantController::class, 'budgetSuggestion'])->name('budget-suggestion'); 
            Route::delete('/history',         [AIAssistantController::class, 'clearHistory'])     ->name('clear-history');
            Route::get('/history',            [AIAssistantController::class, 'getHistory'])       ->name('history');
            Route::post('/export-report', [AIAssistantController::class, 'exportReport'])->name('export-report');
        });
    }); 
}); 
