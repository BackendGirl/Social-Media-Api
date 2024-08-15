<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::post("login",[UserController::class,"index"]);

Route::middleware('auth:sanctum')->group(function () {

    Route::middleware('activeuser')->group(function () {
        
        Route::post('change_password', [UserController::class,'changePassword'])->name('change_password');
        Route::get('get_profile', [UserController::class,'getProfile'])->name('get_profile');
        Route::post('update_profile', [UserController::class,'updateProfile'])->name('update_profile');
        Route::post('logout', [UserController::class,'logout'])->name('logout');
        Route::get('get_notifications', [PostController::class,'get_notifications'])->name('get_notifications');
        Route::get('get_posts', [PostController::class,'get_posts'])->name('get_posts');
        Route::post('like', [PostController::class,'like'])->name('like');
        Route::post('comment', [PostController::class,'comment'])->name('comment');
        Route::post('chat_user', [ChatController::class,'chat_user'])->name('chat_user');
        Route::get('chat_get_user', [ChatController::class,'chat_get_user'])->name('chat_get_user');
        Route::post('comment_delete', [PostController::class,'comment_delete'])->name('comment_delete');
        Route::post('chat_delete', [ChatController::class,'chat_delete'])->name('chat_delete');
        Route::post('/send-notification', [PostController::class, 'sendNotification']);
    });
    Route::middleware('admin')->group(function () {
        Route::get('get_users', [AdminController::class,'getusers'])->name('get_users');
        Route::post('add_post', [AdminController::class,'addPost'])->name('add_post');
        Route::post('add_notification', [AdminController::class,'addNotification'])->name('add_notification');
        Route::post('chat_admin/{user_id}', [ChatController::class,'chat_admin'])->name('chat_admin');
        Route::get('chat_get_admin/{user_id}', [ChatController::class,'chat_get_admin'])->name('chat_get_admin');
        Route::post('block_user', [AdminController::class,'block_user'])->name('block_user');
        Route::post('unblock_user', [AdminController::class,'unblock_user'])->name('unblock_user');
        Route::post('post_delete', [AdminController::class,'post_delete'])->name('post_delete');
        Route::get('all_chat_get_admin', [ChatController::class,'all_chat_get_admin'])->name('all_chat_get_admin');
       
    });

    
});

Route::post('signup', [UserController::class,'signup'])->name('signup');
Route::post('signin', [UserController::class,'signin'])->name('signin');
Route::post('forgot_password', [UserController::class,'forgotPassword'])->name('forgot_password');
Route::post('verify_otp', [UserController::class,'verifyOtp'])->name('verify_otp');
Route::post('testfcm', [UserController::class,'testfcm'])->name('testfcm');
