<?php

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

Route::any('/','SearchController@index');
Route::any('/history','SearchController@history');
Route::any('/test','SearchController@test');
Route::get('/result/{id}','SearchController@result');
Route::get('/rank/{id}','SearchController@rank');

