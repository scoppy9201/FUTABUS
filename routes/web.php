<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\SplitGroupController;
use App\Http\Controllers\GroupMemberController;
use App\Http\Controllers\GroupBalanceController;
use App\Http\Controllers\GroupExpenseController;
use App\Http\Controllers\GroupDebtController;
use App\Http\Controllers\Api\WalletTransferController;
use App\Http\Controllers\Api\QrTransferController;
use Illuminate\Http\Request;

/*
| Web Routes
|
| File này chỉ có nhiệm vụ serve Blade views.
| Toàn bộ data/logic xử lý qua /api/v1/... trong api.php.
|
*/

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/dashboard');
    }
    return view('welcome');
});

/*
| AUTH VIEWS (public — chưa đăng nhập)
*/
Route::middleware('guest')->group(function () {
    Route::get('/login',           fn() => view('auth.AuthForm'))       ->name('login');
    Route::get('/register',        fn() => view('auth.AuthForm'))       ->name('register');
    Route::get('/forgot-password', fn() => view('auth.forgot-password'))->name('password.request');
    Route::get('/verify-code',     fn() => view('auth.verify-code'))    ->name('password.verify.form');
    Route::get('/reset-password',  fn() => view('auth.reset-password')) ->name('password.reset.form');
});

Route::get('/auth/google',          [LoginController::class, 'redirectToGoogle'])    ->name('google.redirect');
Route::get('/auth/google/callback', [LoginController::class, 'handleGoogleCallback'])->name('google.callback');

/*
| AUTHENTICATED VIEWS (cần đăng nhập)
*/
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', fn() => view('dashboard')) ->name('dashboard');
    // Transactions
    Route::get('/transactions', fn() => view('transactions.index')) ->name('transactions.index');
    // Categories
    Route::get('/categories', fn() => view('categories.index'))->name('categories.index');
    // Budgets
    Route::get('/budgets', fn() => view('budgets.index'))->name('budgets.index');
    // Profile
    Route::get('/profile', fn() => view('profile.show'))->name('profile.show');
    // Change password
    Route::get('/change-password', fn() => view('change-password'))->name('change-password.form');
    // Settings
    Route::get('/settings', fn() => view('settings')) ->name('settings.index');
    // Groups (split bill)
    Route::get('/groups', [SplitGroupController::class, 'index'])->name('groups.index');
    Route::post('/groups', [SplitGroupController::class, 'store'])->name('groups.store');
    Route::get('/groups/{group}', [SplitGroupController::class, 'show'])->name('groups.show');
    Route::match(['PUT','PATCH'], '/groups/{group}', [SplitGroupController::class, 'update'])->name('groups.update');
    Route::delete('/groups/{group}', [SplitGroupController::class, 'destroy'])->name('groups.destroy');

    // Group member actions (invite, leave, promote/demote, remove)
    Route::post('/groups/{group}/leave', [GroupMemberController::class, 'leave'])->name('groups.leave');
    Route::post('/groups/{group}/invite', [GroupMemberController::class, 'invite'])->name('groups.invite');
    Route::get('/groups/invite/accept/{token}', [GroupMemberController::class, 'accept'])->name('groups.invite.accept');
    Route::get('/groups/invite/decline/{token}', [GroupMemberController::class, 'decline'])->name('groups.invite.decline');

    Route::post('/groups/{group}/members/{member}/promote', [GroupMemberController::class, 'promote'])->name('groups.members.promote');
    Route::post('/groups/{group}/members/{member}/demote', [GroupMemberController::class, 'demote'])->name('groups.members.demote');
    Route::delete('/groups/{group}/members/{member}', [GroupMemberController::class, 'remove'])->name('groups.members.remove');

    // Toggle visibility of balances (used in Blade)
    Route::post('/groups/{group}/toggle-visibility', [SplitGroupController::class, 'toggleBalanceVisibility'])->name('groups.toggle-visibility');

    // Links to group sub-pages (balance / expense / debt)
    Route::get('/groups/{group}/balance', [GroupBalanceController::class, 'index'])->name('groups.balance.index');
    Route::post('/groups/{group}/balance/propose', [GroupBalanceController::class, 'propose'])->name('groups.balance.propose');
    Route::post('/groups/{group}/balance/{proposal}/approve', [GroupBalanceController::class, 'approve'])->name('groups.balance.approve');
    Route::post('/groups/{group}/balance/{proposal}/reject', [GroupBalanceController::class, 'reject'])->name('groups.balance.reject');
    Route::post('/groups/{group}/balance/{proposal}/cancel', [GroupBalanceController::class, 'cancel'])->name('groups.balance.cancel');
    Route::get('/groups/{group}/expense', [GroupExpenseController::class, 'index'])->name('groups.expense.index');
    Route::post('/groups/{group}/expense', [GroupExpenseController::class, 'store'])->name('groups.expense.store');
    Route::post('/groups/{group}/expense/{proposal}/approve', [GroupExpenseController::class, 'approve'])->name('groups.expense.approve');
    Route::post('/groups/{group}/expense/{proposal}/reject', [GroupExpenseController::class, 'reject'])->name('groups.expense.reject');
    Route::post('/groups/{group}/expense/{proposal}/cancel', [GroupExpenseController::class, 'cancel'])->name('groups.expense.cancel');
    Route::get('/groups/{group}/debt/summary', [GroupDebtController::class, 'summary'])->name('groups.debt.summary');
    Route::post('/groups/{group}/debt', [GroupDebtController::class, 'store'])->name('groups.debt.store');
    Route::post('/groups/{group}/debt/{debt}/settle', [GroupDebtController::class, 'settle'])->name('groups.debt.settle');




    // QR Transfer pages (web views that link to API endpoints)
        Route::prefix('money-wallets/qr')->name('money-wallets.qr.')->group(function () {
            Route::get('/', fn() => view('money-wallets.qr-transfer'))->name('index');
            Route::post('/generate', function(Request $request) {
                $res = app(QrTransferController::class)->generate($request);
                $data = $res instanceof \Illuminate\Http\JsonResponse ? $res->getData(true) : (array) $res;
                if (!empty($data['qr_token'])) {
                    return redirect('/money-wallets/qr/result/' . $data['qr_token']);
                }
                return back()->with('error', $data['message'] ?? 'Không thể tạo QR');
            })->name('generate');

            Route::get('/result/{token}', fn($token) => view('money-wallets.qr-result')) ->name('result');
            Route::get('/scan/{token}', fn($token) => view('money-wallets.qr-scan')) ->name('scan-page');

            Route::post('/{token}/confirm', function(Request $request, $token) {
                $res = app(QrTransferController::class)->confirm($request, $token);
                $data = $res instanceof \Illuminate\Http\JsonResponse ? $res->getData(true) : (array) $res;
                if (!empty($data['success'])) {
                    return redirect()->route('money-wallets.index')->with('success', 'Đã nhận tiền');
                }
                return back()->with('error', $data['message'] ?? 'Không thể xác nhận');
            })->name('confirm');

            Route::post('/{qrTransfer}/cancel', function($qrTransfer) {
                $model = \App\Models\QrTransfer::findOrFail($qrTransfer);
                $res = app(QrTransferController::class)->cancel($model);
                return back();
            })->name('cancel');
        });

        // Money wallets
        Route::get('/money-wallets', fn() => view('money-wallets.index')) ->name('money-wallets.index');
        Route::get('/money-wallets/{moneyWallet}', fn() => view('money-wallets.show')) ->name('money-wallets.show');
    // Wallet transfers (web views + actions that call API controllers)
        Route::prefix('wallet-transfers')->name('wallet-transfers.')->group(function () {
            Route::get('/', fn() => view('money-wallets.transfers.index')) ->name('index');
            Route::post('/', [WalletTransferController::class, 'store']) ->name('store');
            Route::delete('/{walletTransfer}', [WalletTransferController::class, 'destroy']) ->name('destroy');
        });
    // Notifications
    Route::get('/notifications', fn() => view('notifications')) ->name('notifications.index');
    // AI Assistant
    Route::get('/ai-assistant', fn() => view('ai-assistant')) ->name('ai-assistant.index');
    // Currency
    Route::get('/currency', fn() => view('currency.index')) ->name('currency.index');
    // Search
    Route::get('/search', fn() => view('search')) ->name('search');
    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
