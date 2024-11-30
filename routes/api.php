<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentInformationController;
use App\Http\Controllers\TranscriptApplicationsController;
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

Route::prefix('v1')->group(function ()
{
    Route::post('getregistration',    [TranscriptApplicationsController::class, 'getRegistration']);
    Route::apiResource('/transcripts', TranscriptApplicationsController::class);
    Route::apiResource('/studentinfo', StudentInformationController::class);
    Route::get('returncall',[TranscriptApplicationsController::class, 'returnCall']);
});

Route::middleware('auth:api')->prefix('v1')->group(function() {
    Route::post('/logout',[TranscriptApplicationsController::class, 'logout']);
    Route::post('/fetchtranscriptinformation',[TranscriptApplicationsController::class, 'fetchTranscriptInformation']);
    Route::post('/getallpaymentsattempts',[TranscriptApplicationsController::class, 'getAllPaymentsAttempts']);
    Route::post('/addapplication',[TranscriptApplicationsController::class, 'addApplication']);
    Route::post('/makepayments',[TranscriptApplicationsController::class, 'makePayments']);
    Route::post('/completeapplications',[TranscriptApplicationsController::class, 'completeApplications']);
    Route::post('/transcriptrecord',[TranscriptApplicationsController::class, 'transcriptRecord']);

    Route::get('/country',[TranscriptApplicationsController::class, 'country']);
    Route::get('/programmes',[TranscriptApplicationsController::class, 'programmes']);
    Route::get('/getstates/{cid}',[TranscriptApplicationsController::class, 'getStates']);
    Route::get('/getoneapplicantinfo/{guid}',[TranscriptApplicationsController::class, 'getOneApplicantInfo']);
    Route::get('/deleteapplication/{guid}',[TranscriptApplicationsController::class, 'deleteApplication']);
    Route::get('/querytransaction/{tid}',[TranscriptApplicationsController::class, 'queryTransaction']);


});

