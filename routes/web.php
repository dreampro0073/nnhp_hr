<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EntryContoller;
use App\Http\Controllers\MassageController;
use App\Http\Controllers\CloakRoomController;
use App\Http\Controllers\ShiftController;

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


Route::get('/', [UserController::class,'login'])->name("login");
Route::post('/login', [UserController::class,'postLogin']);


Route::get('/logout',function(){
	Auth::logout();
	return Redirect::to('/');
});

Route::group(['middleware'=>'auth'],function(){
	Route::group(['prefix'=>"admin"], function(){
		Route::get('/dashboard',[AdminController::class,'dashboard']);
		Route::get('/reset-password',[UserController::class,'resetPassword']);
		Route::post('/reset-password',[UserController::class,'updatePassword']);
<<<<<<< HEAD
=======
		
>>>>>>> 195b1d102ab728f04b99cb71ab36dad375becfcc

		
		Route::group(['prefix'=>"sitting"], function(){
			Route::get('/',[AdminController::class,'sitting']);
			Route::get('/print/{id?}', [EntryContoller::class,'printPost']);
			Route::get('/print-report', [EntryContoller::class,'printReports']);

		});

		Route::group(['prefix'=>"shift"], function(){
			Route::get('/current',[ShiftController::class,'index']);
			Route::get('/print/{type}',[ShiftController::class,'print']);
		});

		Route::group(['prefix'=>"massage"], function(){
			Route::get('/',[MassageController::class,'massage']);
			Route::get('/print/{id?}', [MassageController::class,'printPost']);
			
		});
<<<<<<< HEAD
		Route::group(['prefix'=>"cloack-rooms"], function(){
			Route::get('/',[CloakRoomController::class,'index']);
			Route::get('/print/{id?}', [CloakRoomController::class,'printPost']);
			
		});	
		Route::group(['prefix'=>"users"], function(){
			Route::get('/',[UserController::class,'users']);
		});	
=======

		Route::group(['prefix'=>"users"], function(){
			Route::get('/',[UserController::class,'users']);
		});	

>>>>>>> 195b1d102ab728f04b99cb71ab36dad375becfcc
	});
});

Route::group(['prefix'=>"api"], function(){
	Route::group(['prefix'=>"sitting"], function(){
		Route::post('/init',[EntryContoller::class,'initEntries']);
		Route::post('/edit-init',[EntryContoller::class,'editEntry']);
		Route::post('/store',[EntryContoller::class,'store']);
		Route::post('/cal-check',[EntryContoller::class,'calCheck']);
		Route::get('/delete/{id}',[EntryContoller::class,'delete']);
		
	});
	Route::group(['prefix'=>"shift"], function(){
		Route::post('/init',[ShiftController::class,'init']);
		Route::post('/prev-init',[ShiftController::class,'prevInit']);

	});

	Route::group(['prefix'=>"massage"], function(){
		Route::post('/init',[MassageController::class,'initMassage']);
		Route::post('/edit-init',[MassageController::class,'editMassage']);
		Route::post('/store',[MassageController::class,'store']);
		Route::get('/delete/{id}',[MassageController::class,'delete']);

	});

	Route::group(['prefix'=>"cloack-rooms"], function(){
		Route::post('/init',[CloakRoomController::class,'initLocker']);
		Route::post('/edit-init',[CloakRoomController::class,'editLocker']);
		Route::post('/store',[CloakRoomController::class,'store']);
		Route::post('/cal-check',[CloakRoomController::class,'calCheck']);
		Route::post('/checkout-init',[CloakRoomController::class,'checkoutInit']);
		Route::post('/checkout-store',[CloakRoomController::class,'checkoutStore']);
		Route::get('/delete/{id}',[CloakRoomController::class,'delete']);

	});
	Route::group(['prefix'=>"users"], function(){
		Route::post('/init',[UserController::class,'initUsers']);
		Route::post('/edit-init',[UserController::class,'editUser']);
		Route::post('/store',[UserController::class,'storeUser']);
	});
});
