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
use App\Http\Controllers\MoneyWalletController;
use App\Http\Controllers\WalletTransferController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\QrTransferController;

/*
| API Routes
|
| Tất cả routes đều có prefix /api/v1
| VD: /api/v1/auth/login, /api/v1/transactions ...
|
| Yêu cầu:
| 1. composer require laravel/sanctum
| 2. php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
| 3. php artisan migrate
| 4. Thêm HasApiTokens vào User model
|
*/

Route::prefix('v1')->name('api.')->group(function () {

    /*
    | AUTH ROUTES (public — không cần token)
    */
    Route::prefix('auth')->name('auth.')->group(function () {

        Route::post('/register', [RegisterController::class, 'register'])->name('register');
        Route::post('/login',    [LoginController::class,    'login'])   ->name('login');

        // Forgot password flow
        Route::prefix('password')->name('password.')->group(function () {
            Route::post('/forgot', [ForgotPasswordController::class, 'sendResetCode'])->name('forgot');  // Gửi code về email
            Route::post('/verify', [ForgotPasswordController::class, 'verifyCode'])   ->name('verify');  // Xác minh code
            Route::post('/reset',  [ForgotPasswordController::class, 'resetPassword'])->name('reset');   // Đặt lại mật khẩu
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

        // Đăng xuất
        Route::post('/auth/logout', [LoginController::class, 'logout'])->name('auth.logout');

        /*
        | DASHBOARD
        */
        Route::prefix('dashboard')->name('dashboard.')->group(function () {
            Route::get('/',       [DashboardController::class, 'index']) ->name('index');
            Route::get('/export', [DashboardController::class, 'export'])->name('export');
        });

        /*
        | SEARCH
        */
        Route::get('/search', [SearchController::class, 'index'])->name('search');

        /*
        | CURRENCY
        */
        Route::prefix('currency')->name('currency.')->group(function () {
            Route::get('/',               [CurrencyController::class, 'index'])        ->name('index');
            Route::post('/convert',       [CurrencyController::class, 'convert'])      ->name('convert');
            Route::get('/history',        [CurrencyController::class, 'history'])      ->name('history');
            Route::delete('/history',     [CurrencyController::class, 'clearHistory']) ->name('history.clear');
            Route::delete('/history/{currencyHistory}', [CurrencyController::class, 'deleteHistory'])->name('history.delete');
        });

        /*
        | PROFILE
        | GET    /profile          → show
        | PATCH  /profile          → update
        | POST   /profile/avatar   → upload avatar
        | DELETE /profile/avatar   → xóa avatar
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
                Route::patch('/',    [EmailSettingController::class, 'update'])  ->name('update');
                Route::post('/test', [EmailSettingController::class, 'testMail'])->name('test');
            });
        });

        /*
        | CATEGORIES
        | GET    /categories               → index
        | POST   /categories               → store
        | GET    /categories/{id}          → show
        | PATCH  /categories/{id}          → update
        | DELETE /categories/{id}          → destroy
        | PATCH  /categories/{id}/status   → toggle status
        */
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/',                    [CategoryController::class, 'index'])       ->name('index');
            Route::post('/',                   [CategoryController::class, 'store'])       ->name('store');
            Route::get('/{category}',          [CategoryController::class, 'show'])        ->name('show');
            Route::patch('/{category}',        [CategoryController::class, 'update'])      ->name('update');
            Route::delete('/{category}',       [CategoryController::class, 'destroy'])     ->name('destroy');
            Route::patch('/{category}/status', [CategoryController::class, 'toggleStatus'])->name('toggle-status');
        });

        /*
        | BUDGETS (Ngân sách)
        | GET    /budgets               → index
        | POST   /budgets               → store
        | GET    /budgets/{id}          → show
        | PATCH  /budgets/{id}          → update
        | DELETE /budgets/{id}          → destroy
        | PATCH  /budgets/{id}/status   → toggle status
        | POST   /budgets/{id}/sync     → sync balance
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
        | GET    /transactions       → index
        | POST   /transactions       → store
        | GET    /transactions/{id}  → show
        | PATCH  /transactions/{id}  → update
        | DELETE /transactions/{id}  → destroy
        */
        Route::apiResource('transactions', TransactionController::class);

        /*
        | GROUPS (Split bill)
        */
        Route::prefix('groups')->name('groups.')->group(function () {

            Route::get('/',             [SplitGroupController::class, 'index'])      ->name('index');
            Route::post('/',            [SplitGroupController::class, 'store'])      ->name('store');
            Route::get('/search-users', [SplitGroupController::class, 'searchUsers'])->name('search-users');
            Route::get('/{group}',      [SplitGroupController::class, 'show'])       ->name('show');
            Route::patch('/{group}',    [SplitGroupController::class, 'update'])     ->name('update');
            Route::delete('/{group}',   [SplitGroupController::class, 'destroy'])    ->name('destroy');
            Route::patch('/{group}/balance-visibility', [SplitGroupController::class, 'toggleBalanceVisibility'])->name('toggle-visibility');

            // Members
            Route::prefix('/{group}/members')->name('members.')->group(function () {
                Route::post('/',               [GroupMemberController::class, 'invite']) ->name('invite');
                Route::delete('/leave',        [GroupMemberController::class, 'leave'])  ->name('leave');
                Route::delete('/{member}',     [GroupMemberController::class, 'remove']) ->name('remove');
                Route::patch('/{member}/role', [GroupMemberController::class, 'promote'])->name('promote');
            });

            // Balance & Proposals
            Route::prefix('/{group}/balance')->name('balance.')->group(function () {
                Route::get('/',                               [GroupBalanceController::class, 'index'])  ->name('index');
                Route::post('/proposals',                     [GroupBalanceController::class, 'propose'])->name('propose');
                Route::patch('/proposals/{proposal}/approve', [GroupBalanceController::class, 'approve'])->name('approve');
                Route::patch('/proposals/{proposal}/reject',  [GroupBalanceController::class, 'reject']) ->name('reject');
                Route::patch('/proposals/{proposal}/cancel',  [GroupBalanceController::class, 'cancel']) ->name('cancel');
            });

            // Expenses
            Route::prefix('/{group}/expenses')->name('expense.')->group(function () {
                Route::get('/',                               [GroupExpenseController::class, 'index'])  ->name('index');
                Route::post('/',                              [GroupExpenseController::class, 'store'])  ->name('store');
                Route::patch('/proposals/{proposal}/approve', [GroupExpenseController::class, 'approve'])->name('approve');
                Route::patch('/proposals/{proposal}/reject',  [GroupExpenseController::class, 'reject']) ->name('reject');
                Route::patch('/proposals/{proposal}/cancel',  [GroupExpenseController::class, 'cancel']) ->name('cancel');
            });

            // Debts
            Route::prefix('/{group}/debts')->name('debt.')->group(function () {
                Route::post('/',               [GroupDebtController::class, 'store'])  ->name('store');
                Route::get('/summary',         [GroupDebtController::class, 'summary'])->name('summary');
                Route::patch('/{debt}/settle', [GroupDebtController::class, 'settle']) ->name('settle');
            });
        });

        /*
        | NOTIFICATIONS
        | GET    /notifications                       → index
        | GET    /notifications/dropdown              → 5-10 gần nhất
        | GET    /notifications/by-date               → group theo ngày
        | GET    /notifications/badge                 → số unread
        | PATCH  /notifications/read-all              → đánh dấu tất cả đã đọc
        | PATCH  /notifications/{id}/read             → đánh dấu 1 đã đọc
        | POST   /notifications/invite-action/{token} → xử lý invite
        */
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/',                       [NotificationController::class, 'index'])            ->name('index');
            Route::get('/dropdown',               [NotificationController::class, 'dropdown'])         ->name('dropdown');
            Route::get('/by-date',                [NotificationController::class, 'byDate'])           ->name('by-date');
            Route::get('/badge',                  [NotificationController::class, 'badge'])            ->name('badge');
            Route::patch('/read-all',             [NotificationController::class, 'markAllRead'])      ->name('mark-all-read');
            Route::patch('/{notification}/read',  [NotificationController::class, 'markRead'])         ->name('mark-read');
            Route::post('/invite-action/{token}', [NotificationController::class, 'handleInviteAction'])->name('invite-action');
        });

        /*
        | MONEY WALLETS (Ví tiền thực)
        | GET    /money-wallets                → index
        | POST   /money-wallets                → store
        | GET    /money-wallets/{id}           → show
        | PATCH  /money-wallets/{id}           → update
        | DELETE /money-wallets/{id}           → destroy
        | POST   /money-wallets/{id}/restore   → khôi phục ví đã xóa
        | PATCH  /money-wallets/{id}/balance   → điều chỉnh số dư
        */
        Route::prefix('money-wallets')->name('money-wallets.')->group(function () {
            Route::get('/',                        [MoneyWalletController::class, 'index'])  ->name('index');
            Route::post('/',                       [MoneyWalletController::class, 'store'])  ->name('store');
            Route::get('/{moneyWallet}',           [MoneyWalletController::class, 'show'])   ->name('show');
            Route::patch('/{moneyWallet}',         [MoneyWalletController::class, 'update']) ->name('update');
            Route::delete('/{moneyWallet}',        [MoneyWalletController::class, 'destroy'])->name('destroy');
            Route::post('/{moneyWallet}/restore',  [MoneyWalletController::class, 'restore'])->name('restore');
            Route::patch('/{moneyWallet}/balance', [MoneyWalletController::class, 'adjust']) ->name('adjust');
        });

        /*
        | QR TRANSFERS (Chuyển tiền qua QR)
        | Tách ra khỏi money-wallets vì QR không phải sub-resource của 1 wallet cụ thể
        |
        | GET    /qr-transfers                     → index (danh sách QR)
        | POST   /qr-transfers                     → store (tạo QR mới)
        | DELETE /qr-transfers/{id}                → cancel (huỷ QR)
        | GET    /qr-transfers/scan/{token}        → xem trang scan
        | POST   /qr-transfers/scan/{token}/confirm → xác nhận chuyển tiền
        */
        Route::prefix('qr-transfers')->name('qr-transfers.')->group(function () {
            Route::get('/',                       [QrTransferController::class, 'index'])   ->name('index');
            Route::post('/',                      [QrTransferController::class, 'generate'])->name('store');
            Route::delete('/{qrTransfer}',        [QrTransferController::class, 'cancel'])  ->name('destroy');
            Route::get('/scan/{token}',           [QrTransferController::class, 'scanPage'])->name('scan');
            Route::post('/scan/{token}/confirm',  [QrTransferController::class, 'confirm']) ->name('confirm');
        });

        /*
        | WALLET TRANSFERS (Chuyển tiền giữa các ví)
        | GET    /wallet-transfers       → index
        | POST   /wallet-transfers       → store
        | DELETE /wallet-transfers/{id}  → destroy
        */
        Route::prefix('wallet-transfers')->name('wallet-transfers.')->group(function () {
            Route::get('/',                    [WalletTransferController::class, 'index'])  ->name('index');
            Route::post('/',                   [WalletTransferController::class, 'store'])  ->name('store');
            Route::delete('/{walletTransfer}', [WalletTransferController::class, 'destroy'])->name('destroy');
        });

        /*
        | AI ASSISTANT
        | GET    /ai/suggestions  → gợi ý tự động
        | GET    /ai/insights     → phân tích tổng quan
        | POST   /ai/chat         → chat với AI
        | POST   /ai/analyze      → phân tích theo yêu cầu
        | DELETE /ai/history      → xóa lịch sử chat
        */
        Route::prefix('ai')->name('ai.')->group(function () {
            Route::get('/suggestions', [AIAssistantController::class, 'suggestions'])->name('suggestions');
            Route::get('/insights',    [AIAssistantController::class, 'insights'])   ->name('insights');
            Route::post('/chat',       [AIAssistantController::class, 'chat'])       ->name('chat');
            Route::post('/analyze',    [AIAssistantController::class, 'analyze'])    ->name('analyze');
            Route::delete('/history',  [AIAssistantController::class, 'clearHistory'])->name('clear-history');
        });
    }); // end auth:sanctum
});