<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ReportController;

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


/**
 * | Routes for API masters, api searchable and api edit
 * | CreatedOn-27-08-2022
 * | Api No Format- RP+Controller No+ApiNo
 */

/**
 * | Controller No 01
 */

Route::controller(MasterController::class)->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Resource master
    |--------------------------------------------------------------------------
    */
    Route::post('v1/resource/save', 'resourceSave');                    // 01
    Route::post('v1/resource/update', 'resourceUpdate');                // 02
    Route::post('v1/resource/view', 'getresource');                     // 03
    Route::post('v1/resource/deactivate', 'deactivateResource');        // 04

    /*
    |--------------------------------------------------------------------------
    | Search Group master
    |--------------------------------------------------------------------------
    */
    Route::post('v1/group/save', 'saveGroup');                          // 05                             
    Route::post('v1/group/update', 'updateGroup');                      // 06
    Route::post('v1/group/view', 'getGroup');                           // 07
    Route::post('v1/group/deactivate', 'deactivateGroup');              // 08          

    /*
    |--------------------------------------------------------------------------
    | String master
    |--------------------------------------------------------------------------
    */
    Route::post('v1/string/save', 'saveString');                       // 09       
    Route::post('v1/string/update', 'updateString');                   // 11
    Route::post('v1/string/view', 'getString');                         // 11
    Route::post('v1/string/deactivate', 'deactivateString');            // 12

    /*
    |--------------------------------------------------------------------------
    | Tempalte master
    |--------------------------------------------------------------------------
    */
    Route::post('v1/template/save', 'saveTemplate');                    // 13
    Route::post('v1/template/update', 'updateTemplate');                // 14
    Route::post('v1/template/view', 'getTemplateById');                 // 15
    Route::post('v1/template/list', 'templateList');                    // 16

    /*
    |--------------------------------------------------------------------------
    | Menu List
    |--------------------------------------------------------------------------
    */
    Route::post('v1/getmenu', 'MenuList');                              // 17


    /**
     * | Module Masters
     */
    Route::post('v1/module/list', 'moduleList');                       // 18
});


/**
 * | Report Generation Controller 
 * | Controller No = 02
 */
Route::controller(ReportController::class)->group(function () {
    Route::post('v1/report/generate', 'generateReport');               // 01
    Route::post('v1/report/query-result', 'queryResult');                         // Get Report Query Result(02)
    Route::post('v1/report/search-report-generate', 'generateSearchReport');      // Generate Search Report(03)
});
