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

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', 'StaticPagesController@home')->name('home');
Route::get('/help', 'StaticPagesController@help')->name('help');
Route::get('/about', 'StaticPagesController@about')->name('about');

Route::get('signup', 'UsersController@create')->name('signup');
Route::resource('users','UsersController');

Route::get('login', 'SessionsController@create')->name('login');
Route::post('login', 'SessionsController@store')->name('login');
Route::delete('logout', 'SessionsController@destroy')->name('logout');

// 邮箱确认路由
Route::get('signup/confirm/{token}', 'UsersController@confirmEmail')->name('confirm_email');

// 填写Email的表单
Route::get('password/reset', 'PasswordController@showLinkRequestForm')->name('password.request');
//  处理表单提交，成功的话就发送邮件，附带Token链接
Route::post('password/email', 'PasswordController@sendResetLinkEmail')->name('password.email');

// 显示更新密码的表单，包含token
Route::get('password/reset/{token}', 'PasswordController@showResetForm')->name('password.reset');
// 对提交过来的token和Email数据进行配对，正确的话更新密码
Route::post('password/reset', 'PasswordController@reset')->name('password.update');


// Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
