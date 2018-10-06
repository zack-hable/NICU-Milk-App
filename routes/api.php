<?php

use Illuminate\Http\Request;

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
//Route::middleware(['auth:api'])->group(function () {
    // return the current user
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    // baby management
    Route::prefix('baby')->group(function () {
        // return all of the babies
        Route::get('', 'BabyController@listAll');
        // get the recommended bottle for a baby
        Route::get('{baby}/bottle/best', 'BabyController@bestBottle');
        // returns a specific baby's bottles
        Route::get('{baby}/bottle', 'BabyController@listBottle');
        // add a new baby
        Route::put('', 'BabyController@insert');
        // remove a baby
        Route::delete('{baby}', 'BabyController@delete');
    });
    // bottle management
    Route::prefix('bottle')->group(function () {
        // return all of the bottles
        Route::get('', 'BottleController@listAll');
        // add a new bottle
        Route::put('', 'BottleController@insert');
        // remove a bottle
        Route::delete('{bottle}', 'BottleController@delete');
    });
//});
