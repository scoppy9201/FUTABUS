<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
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
use App\Http\Controllers\LoginController as AuthController;

/*
| Để dùng file này:
| 1. Cài Sanctum: composer require laravel/sanctum
| 2. Publish config: php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
| 3. Chạy migrate: php artisan migrate
| 4. Thêm HasApiTokens vào User model
| 5. Các Controller cần đổi return view() → return response()->json()
*/

/*
| VERSION PREFIX
| Tất cả routes đều có prefix /api/monaxe để dễ versioning sau này.
| VD: /api/monaxe/auth/login, /api/monaxe/transactions ...
*/

Route::prefix('monaxe')->name('api.')->group(function () {

    /*
    AUTH ROUTES (public - không cần token)
    */
    Route::prefix('auth')->name('auth.')->group(function () {

        // Đăng ký
        Route::post('/register', [RegisterController::class, 'register'])->name('register');

        // Đăng nhập
        Route::post('/login', [LoginController::class, 'login'])->name('login');

        // Google OAuth
        Route::get('/google',          [LoginController::class, 'redirectToGoogle'])    ->name('google.redirect');
        Route::get('/google/callback', [LoginController::class, 'handleGoogleCallback'])->name('google.callback');

        // Forgot password flow
        Route::prefix('password')->name('password.')->group(function () {
            Route::post('/forgot',  [ForgotPasswordController::class, 'sendResetCode'])->name('forgot');  // Gửi code về email
            Route::post('/verify',  [ForgotPasswordController::class, 'verifyCode'])   ->name('verify');  // Xác minh code
            Route::post('/reset',   [ForgotPasswordController::class, 'resetPassword'])->name('reset');   // Đặt lại mật khẩu
        });
    });

    /*
    AUTHENTICATED ROUTES (cần token Sanctum)
    */
    Route::middleware('auth:sanctum')->group(function () {

        // Đăng xuất
        Route::post('/auth/logout', [LoginController::class, 'logout'])->name('auth.logout');

        /*
        | DASHBOARD
        */
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');

        /*
        | SEARCH
        */
        Route::get('/search', [SearchController::class, 'index'])->name('search');

        /*
        | CURRENCY
        */
        Route::get('/currency', [CurrencyController::class, 'index'])->name('currency.index');

        /*
        | PROFILE
        | GET    /profile         → show
        | PATCH  /profile         → update
        | POST   /profile/avatar  → upload avatar
        | DELETE /profile/avatar  → xóa avatar
        */
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/',        [ProfileController::class, 'show'])        ->name('show');
            Route::patch('/',      [ProfileController::class, 'update'])      ->name('update');
            Route::post('/avatar', [ProfileController::class, 'updateAvatar'])->name('avatar.update');
            Route::delete('/avatar',[ProfileController::class, 'deleteAvatar'])->name('avatar.delete');
        });

        /*
        | CHANGE PASSWORD
        | POST /password/change
        */
        Route::post('/password/change', [ChangePasswordController::class, 'changePassword'])->name('password.change');

        /*
        | SETTINGS
        */
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingsController::class, 'index'])->name('index');

            // Email settings
            Route::prefix('email')->name('email.')->group(function () {
                Route::patch('/',     [EmailSettingController::class, 'update'])  ->name('update');
                Route::post('/test',  [EmailSettingController::class, 'testMail'])->name('test');
            });
        });

        /*
        | CATEGORIES
        | GET    /categories           → index
        | POST   /categories           → store
        | GET    /categories/{id}      → show
        | PATCH  /categories/{id}      → update
        | DELETE /categories/{id}      → destroy
        | PATCH  /categories/{id}/status → toggle status (dùng PATCH thay POST cho đúng chuẩn REST)
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
        | TRANSACTIONS
        | GET    /transactions      → index
        | POST   /transactions      → store
        | GET    /transactions/{id} → show
        | PATCH  /transactions/{id} → update
        | DELETE /transactions/{id} → destroy
        */
        Route::apiResource('transactions', TransactionController::class);

        /*
        | GROUPS (Split bill)
        */
        Route::prefix('groups')->name('groups.')->group(function () {

            Route::get('/',             [SplitGroupController::class, 'index'])                  ->name('index');
            Route::post('/',            [SplitGroupController::class, 'store'])                  ->name('store');
            Route::get('/search-users', [SplitGroupController::class, 'searchUsers'])            ->name('search-users');
            Route::get('/{group}',      [SplitGroupController::class, 'show'])                   ->name('show');
            Route::patch('/{group}',    [SplitGroupController::class, 'update'])                 ->name('update');
            Route::delete('/{group}',   [SplitGroupController::class, 'destroy'])                ->name('destroy');
            Route::patch('/{group}/balance-visibility', [SplitGroupController::class, 'toggleBalanceVisibility'])->name('toggle-visibility');

            // Members
            Route::prefix('/{group}/members')->name('members.')->group(function () {
                Route::post('/',                [GroupMemberController::class, 'invite'])  ->name('invite');
                Route::delete('/leave',         [GroupMemberController::class, 'leave'])   ->name('leave');
                Route::delete('/{member}',      [GroupMemberController::class, 'remove'])  ->name('remove');
                Route::patch('/{member}/role',  [GroupMemberController::class, 'promote']) ->name('promote'); // promote/demote gộp vào 1 endpoint PATCH /role
            });

            // Balance & Proposals
            Route::prefix('/{group}/balance')->name('balance.')->group(function () {
                Route::get('/',                              [GroupBalanceController::class, 'index'])  ->name('index');
                Route::post('/proposals',                    [GroupBalanceController::class, 'propose'])->name('propose');
                Route::patch('/proposals/{proposal}/approve',[GroupBalanceController::class, 'approve'])->name('approve');
                Route::patch('/proposals/{proposal}/reject', [GroupBalanceController::class, 'reject']) ->name('reject');
                Route::patch('/proposals/{proposal}/cancel', [GroupBalanceController::class, 'cancel']) ->name('cancel');
            });

            // Expenses
            Route::prefix('/{group}/expenses')->name('expense.')->group(function () {
                Route::get('/',                              [GroupExpenseController::class, 'index'])  ->name('index');
                Route::post('/',                             [GroupExpenseController::class, 'store'])  ->name('store');
                Route::patch('/proposals/{proposal}/approve',[GroupExpenseController::class, 'approve'])->name('approve');
                Route::patch('/proposals/{proposal}/reject', [GroupExpenseController::class, 'reject']) ->name('reject');
                Route::patch('/proposals/{proposal}/cancel', [GroupExpenseController::class, 'cancel']) ->name('cancel');
            });

            // Debts
            Route::prefix('/{group}/debts')->name('debt.')->group(function () {
                Route::post('/',              [GroupDebtController::class, 'store'])  ->name('store');
                Route::get('/summary',        [GroupDebtController::class, 'summary'])->name('summary');
                Route::patch('/{debt}/settle',[GroupDebtController::class, 'settle']) ->name('settle');
            });
        });

        /*
        | NOTIFICATIONS
        | GET    /notifications              → index (tất cả)
        | GET    /notifications/dropdown     → dropdown (5-10 gần nhất)
        | GET    /notifications/by-date      → group theo ngày
        | GET    /notifications/badge        → số unread
        | PATCH  /notifications/{id}/read    → đánh dấu đã đọc (PATCH thay POST)
        | PATCH  /notifications/read-all     → đánh dấu tất cả đã đọc
        | POST   /notifications/invite-action/{token} → xử lý invite
        */
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/',                         [NotificationController::class, 'index'])         ->name('index');
            Route::get('/dropdown',                 [NotificationController::class, 'dropdown'])      ->name('dropdown');
            Route::get('/by-date',                  [NotificationController::class, 'byDate'])        ->name('by-date');
            Route::get('/badge',                    [NotificationController::class, 'badge'])         ->name('badge');
            Route::patch('/read-all',               [NotificationController::class, 'markAllRead'])   ->name('mark-all-read');
            Route::patch('/{notification}/read',    [NotificationController::class, 'markRead'])      ->name('mark-read');
            Route::post('/invite-action/{token}',   [NotificationController::class, 'handleInviteAction'])->name('invite-action');
        });

        /*
        | MONEY WALLETS (Ví tiền thực)
        | GET    /money-wallets           → index
        | POST   /money-wallets           → store
        | GET    /money-wallets/{id}      → show
        | PATCH  /money-wallets/{id}      → update
        | DELETE /money-wallets/{id}      → destroy
        | POST   /money-wallets/{id}/restore → khôi phục ví đã xóa
        | PATCH  /money-wallets/{id}/balance → điều chỉnh số dư (adjust)
        */
        Route::prefix('money-wallets')->name('money-wallets.')->group(function () {
            Route::get('/',                          [MoneyWalletController::class, 'index'])  ->name('index');
            Route::post('/',                         [MoneyWalletController::class, 'store'])  ->name('store');
            Route::get('/{moneyWallet}',             [MoneyWalletController::class, 'show'])   ->name('show');
            Route::patch('/{moneyWallet}',           [MoneyWalletController::class, 'update']) ->name('update');
            Route::delete('/{moneyWallet}',          [MoneyWalletController::class, 'destroy'])->name('destroy');
            Route::post('/{moneyWallet}/restore',    [MoneyWalletController::class, 'restore'])->name('restore');
            Route::patch('/{moneyWallet}/balance',   [MoneyWalletController::class, 'adjust']) ->name('adjust');

            // QR Transfer
            Route::prefix('/qr')->name('qr.')->group(function () {
                Route::get('/',                      [QrTransferController::class, 'index'])   ->name('index');
                Route::post('/generate',             [QrTransferController::class, 'generate'])->name('generate');
                Route::delete('/{qrTransfer}',       [QrTransferController::class, 'cancel'])  ->name('cancel');   
                Route::get('/scan/{token}',          [QrTransferController::class, 'scanPage'])->name('scan');
                Route::post('/scan/{token}/confirm', [QrTransferController::class, 'confirm']) ->name('confirm');
            });
        });

        /*
        | WALLET TRANSFERS (Chuyển tiền giữa các ví)
        | GET    /wallet-transfers      → index
        | POST   /wallet-transfers      → store
        | DELETE /wallet-transfers/{id} → destroy
        */
        Route::prefix('wallet-transfers')->name('wallet-transfers.')->group(function () {
            Route::get('/',                    [WalletTransferController::class, 'index'])  ->name('index');
            Route::post('/',                   [WalletTransferController::class, 'store'])  ->name('store');
            Route::delete('/{walletTransfer}', [WalletTransferController::class, 'destroy'])->name('destroy');
        });

        /*
        | AI ASSISTANT
        | GET  /ai/suggestions → gợi ý
        | GET  /ai/insights    → phân tích
        | POST /ai/chat        → chat
        | POST /ai/analyze     → phân tích theo yêu cầu
        | DELETE /ai/history   → xóa lịch sử chat (DELETE thay POST/clear)
        */
        Route::prefix('ai')->name('ai.')->group(function () {
            Route::get('/suggestions',  [AIAssistantController::class, 'suggestions'])->name('suggestions');
            Route::get('/insights',     [AIAssistantController::class, 'insights'])   ->name('insights');
            Route::post('/chat',        [AIAssistantController::class, 'chat'])       ->name('chat');
            Route::post('/analyze',     [AIAssistantController::class, 'analyze'])    ->name('analyze');
            Route::delete('/history',   [AIAssistantController::class, 'clearHistory'])->name('clear-history');
        });
    }); // end auth:sanctum

    Route::prefix('groups')->group(function () {
        Route::get('/invitations/{token}/accept',  [GroupMemberController::class, 'accept']) ->name('invite.accept');
        Route::get('/invitations/{token}/decline', [GroupMemberController::class, 'decline'])->name('invite.decline');
    });
}); 
