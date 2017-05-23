<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/


Route::get('/','ReportingController@index');
Route::post('/store-data/{platform}','ReportingController@storeData');
Route::get('/final-data','ReportingController@getFinalData');
Route::get('/pr-view','ReportingController@getPRView');
Route::post('/add-pr','ReportingController@addPRDetails');
Route::get('/exportdata-excel','ReportingController@getFinalPR_Report');
Route::post('/importToData','ReportingController@importToDB');
Route::get('/update/{type}','ReportingController@updateon_screen');
Route::get('/download-excel/{type}','ReportingController@download_miss_data_excel');
Route::get('get-lastmodified-date/{pt_name}','ReportingController@getEndDate');
Route::get('downloads/{type}','ReportingController@defaultTemplates');
Route::get('/update-row','ReportingController@onScreenAction');