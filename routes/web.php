<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\AdminController;
use App\Http\Middleware\IsAdmin; // <-- この行を追加

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

// Breezeが作成したゲスト向けのルート（ログイン画面など）
Route::get('/', function () {
    return view('welcome');
});

// Breezeが作成したダッシュボードのルート
Route::get('/dashboard', function () {
    // ★ログイン後のトップページをチャット画面に変更します
    return redirect()->route('chat.index');
})->middleware(['auth', 'verified'])->name('dashboard');


// チャット関連のルート
Route::middleware('auth')->group(function () {
    // ログイン後の「/」アクセスもチャット画面が表示されるように変更
    Route::get('/', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat/new', [ChatController::class, 'startNewConversation'])->name('chat.new');
    Route::get('/chat/{conversation}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{conversation}', [ChatController::class, 'chat'])->name('chat.post');
});

// プロフィール関連のルート
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ▼▼▼ ミドルウェアの指定をクラス名に修正します ▼▼▼
Route::middleware(['auth', IsAdmin::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
});


require __DIR__.'/auth.php';
