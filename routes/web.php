<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/clear_cache', function () {
    try {
        \Artisan::call('cache:clear');
        \Artisan::call('config:clear');
        \Artisan::call('view:clear');
        \Artisan::call('route:clear');
        \Artisan::call('clear-compiled');
        \Artisan::call('config:cache');
    } catch (\Exception $e) {
        return "Error while clearing cache: " . $e->getMessage();
    }
  
    return "All caches have been cleared successfully!";
  });

  Route::get('testmail', function(){
    
  try{
      Mail::send([], [], function ($message) {
        $message->to('prarthana120901@gmail.com')
                ->subject('Subject of the email')
                ->setBody('This is the content of the email', 'text/plain');
    });
  }catch(Exception $e){
    return [config('mail'),$e];
  }
  
  return response()->json(['message' => 'Email sent successfully','config'=>config('mail')]);
  });
