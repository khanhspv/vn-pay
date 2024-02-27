<?php

use App\Http\Controllers\PaymentController;
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

//Route::get('/', function () {
//    return view('welcome');
//});

Route::get('/', [PaymentController::class, 'list'])->name('payment.list');
Route::get('/create', [PaymentController::class, 'createPay'])->name('payment.create-pay');
Route::post('/pay', [PaymentController::class, 'pay'])->name('payment.pay');
Route::get('/return-vnpay', [PaymentController::class, 'returnPay'])->name('payment.return-pay');

