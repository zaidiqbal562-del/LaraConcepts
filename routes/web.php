<?php

// use App;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SMSController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PushNotificationController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

//Projects route
Route::middleware('auth')->group(function(){
Route::get('/projects',[ProjectController::class,'index'])->name('projects.index');
Route::get('/projects/create',[ProjectController::class,'create'])->name('projects.create');
Route::post('/projects',[ProjectController::class,'store'])->name('projects.store');
Route::get('/projects/{project}/edit',[ProjectController::class,'edit'])->name('projects.edit');
Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
});
//project search route
Route::get('/projects/search',[ProjectController::class,'search'])->name('projects.search');
Route::post('/projects/search',[ProjectController::class,'search'])->name('projects.search');

//Push Notification route 
Route::post('/save-fcm-token',[PushNotificationController::class,'saveFcmtoken'])->name('save-fcm-token');

//Product
Route::get('/products',[ProductController::class,'index'])->name('products.index');
Route::middleware('auth')->get('/users', [UserController::class, 'index'])->name('users.index');
Route::middleware('auth')->get('/payouts', [PayoutController::class, 'index'])->name('payouts.index');
Route::middleware('auth')->post('/payouts', [PayoutController::class, 'store'])->name('payouts.store');
Route::post('/orders/create',[OrderController::class,'create'])->name('orders.create');
Route::post('/payments/verify',[OrderController::class,'verify'])->name('payments.verify');

//Listing
Route::get('/listing',[ListController::class,'index'])->name('listing.index');
Route::get('/check-age1',[ListController::class,'checkAge'])->name('age.check');
Route::post('/check-age',[ListController::class,'checkAge'])->name('check.age');

//SMS
Route::get('/sms',[SMSController::class,'index'])->name('sms.index');
Route::get('/send-sms', [SMSController::class, 'sendSms']);

//callback
Route::get('/callback/project',[ProjectController::class,'callback'])->name('projects.callback');
