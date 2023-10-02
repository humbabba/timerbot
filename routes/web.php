<?php

use Illuminate\Support\Facades\Route;
use App\Models\Timer;

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
    $timers = Timer::all();
    return view('welcome')->with(['timers' => $timers]);
});
Route::resource('timers', 'App\Http\Controllers\TimerController');
Route::get('timers/{id}/info', 'App\Http\Controllers\TimerController@info')->name('timers.info');
Route::post('timers/event', 'App\Http\Controllers\TimerController@event')->name('timers.event');
