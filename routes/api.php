<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\EcommerceController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/image/{name}',  [AdminController::class,'getImageUrl']);

Route::group(['prefix' => 'auth'], function () {
    //login and register
    Route::post('/register', [UserController::class,'register']);
    Route::post('/login', [UserController::class,'login']);
    Route::post('/change-password', [UserController::class,'changePassword']);
    Route::post('/reset-password', [UserController::class,'resetPassword']);
    Route::post('/update-account', [UserController::class,'updateAccount']);


});//end
Route::group(['prefix' => 'dashboard'], function () {
    //login and register
    Route::post('/add-product', [AdminController::class,'addProduct']);
    Route::post('/add-image', [AdminController::class,'addImage']);
    Route::post('/update-product', [AdminController::class,'updateProduct']);
    Route::post('/delete-product', [AdminController::class,'deleteCategory']);
    Route::post('/add-category', [AdminController::class,'addCategory']);
    Route::post('/update-category', [AdminController::class,'updateCategory']);
    Route::post('/delete-category', [AdminController::class,'deleteCategory']);


});//end
Route::group(['prefix' => 'commerce'], function () {
    //login and register
    Route::get('/products', [AdminController::class,'getProduct']);
    Route::post('/add-cart', [EcommerceController::class,'addCart']);
    Route::post('/paid-product', [EcommerceController::class,'  ']);
    Route::post('/product-in-cart', [EcommerceController::class,'getProductInCart']);
    Route::post('/delete-product-in-cart', [EcommerceController::class,'retrieveProduct']);
    Route::post('/paid-product', [EcommerceController::class,'getProductPaid']);
    Route::post('/add-comment', [EcommerceController::class,'addComment']);
    Route::post('/detail-product', [EcommerceController::class,'detailProduct']);
    Route::get('/categories', [AdminController::class,'getCategory']);
    Route::post('/search-product', [EcommerceController::class,'seachProductByCategory']);






});//end



