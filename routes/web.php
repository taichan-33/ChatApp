<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\AdminController;
use App\Http\Middleware\IsAdmin; // IsAdminミドルウェアをインポート
use App\Http\Controllers\Auth\AdminLoginController; // AdminLoginControllerをインポート

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// --- 一般ユーザー向けルート ---

// トップページ
Route::get('/', function () {
    // ログインしている場合はチャット画面へ、していない場合はウェルカムページへ
    if (auth()->check()) {
        return redirect()->route('chat.index');
    }
    return view('welcome');
});

// 認証済みユーザーのみがアクセスできるルート
Route::middleware('auth')->group(function () {
    // ダッシュボード（実質チャット画面へのリダイレクト）
    Route::get('/dashboard', function () {
        return redirect()->route('chat.index');
    })->name('dashboard');

    // チャット関連
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat/new', [ChatController::class, 'startNewConversation'])->name('chat.new');
    Route::get('/chat/{conversation}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{conversation}', [ChatController::class, 'chat'])->name('chat.post');
    
    // プロフィール関連
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


// --- 管理者向けルート ---

// 'admin' というURLプレフィックスと 'admin.' というルート名プレフィックスを適用
Route::prefix('admin')->name('admin.')->group(function () {
    // ログイン画面（認証不要）
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('login');
    // ログイン処理（認証不要）
    Route::post('/login', [AdminLoginController::class, 'login']);
    // ログアウト処理
    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');
    
    // IsAdminミドルウェアを適用し、管理者認証が必要なルートを定義
    Route::middleware(IsAdmin::class)->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
    });
});


// Breezeの認証ルートを読み込む
require __DIR__.'/auth.php';