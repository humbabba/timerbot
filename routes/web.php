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
Route::resource('timers', 'TimerController')->except([
    'show'
]);
Route::get('timers/{name}', 'App\Http\Controllers\TimerController@show')->name('timers.show');
Route::get('timers/{name}/info', 'App\Http\Controllers\TimerController@info')->name('timers.info');
Route::get('timers/{name}/admin', 'App\Http\Controllers\TimerController@edit')->name('timers.edit');
Route::post('timers/update', 'App\Http\Controllers\TimerController@update')->name('timers.update');
