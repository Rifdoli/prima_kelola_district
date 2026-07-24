<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AssessmentQuestionController;
use App\Http\Controllers\Api\AssessmentTrackingController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\OrganizationTypeController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SelfAssessmentController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VerificationController;

use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\AssessmentSelfController;
use App\Http\Controllers\Api\AssessmentOnDeskController;
use App\Http\Controllers\Api\AssessmentOnSiteController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::prefix('questions')
        ->name('questions.')
        ->controller(QuestionController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{id}', 'show')->name('show');
            Route::post('/', 'store')->name('store')->middleware('admin');
            Route::put('/{id}', 'update')->name('update')->middleware('admin');
            Route::patch('/{id}/criterias', 'updateCriterias')->name('criterias.update')->middleware('admin');
            Route::delete('/{id}', 'destroy')->name('destroy')->middleware('admin');
            Route::get('/trash', 'showTrashes')->name('trash.index');
            Route::get('/trash/{id}', 'showTrash')->name('trash.show');
            Route::patch('/trash', 'restoreTrashes')->name('trash.restore')->middleware('admin');
            Route::delete('/trash', 'cleanTrashes')->name('trash.destroy')->middleware('admin');
        });

    Route::get('/assessments/tracking', [AssessmentTrackingController::class, 'index'])
        ->name('assessments.tracking');

    Route::prefix('assessments/sa')
        ->name('assessments.sa.')
        ->controller(AssessmentSelfController::class)
        ->group(function () {
            Route::get('/{period}', 'index')->name('index');
            Route::post('/{period}', 'store')->name('store');
            Route::post('/{period}/draft', 'storeDraft')->name('draft.store');
            Route::post('/{period}/evidence', 'storeEvidence')->name('evidence.store');
            // Route::delete('/{period}/evidence', 'destroyEvidence')->name('evidence.destroy');
            Route::get('/{period}/example', 'showExampleAnswersBody')->name('example.show')->middleware('devonly:dev,debug');
        });

    Route::prefix('assessments/oda')
        ->name('assessments.oda.')
        ->controller(AssessmentOnDeskController::class)
        ->group(function () {
            Route::get('/{period}/{orgId}', 'index')->name('index')->whereNumber('orgId');
            Route::post('/{period}/{orgId}', 'store')->name('store')->whereNumber('orgId');
            Route::post('/{period}/{orgId}/draft', 'storeDraft')->name('draft.store')->whereNumber('orgId');
            Route::post('/{period}/{orgId}/evidence', 'storeEvidence')->name('evidence.store')->whereNumber('orgId');
            Route::get('/{period}/{orgId}/example', 'showExampleAnswersBody')->name('example.show')->whereNumber('orgId')->middleware('devonly:dev,debug');
        });

    Route::prefix('assessments/osa')
        ->name('assessments.osa.')
        ->controller(AssessmentOnSiteController::class)
        ->group(function () {
            Route::get('/{period}/{orgId}', 'index')->name('index')->whereNumber('orgId');
            Route::post('/{period}/{orgId}', 'store')->name('store')->whereNumber('orgId');
            Route::post('/{period}/{orgId}/draft', 'storeDraft')->name('draft.store')->whereNumber('orgId');
            Route::post('/{period}/{orgId}/evidence', 'storeEvidence')->name('evidence.store')->whereNumber('orgId');
            Route::get('/{period}/{orgId}/example', 'showExampleAnswersBody')->name('example.show')->whereNumber('orgId')->middleware('devonly:dev,debug');
        });

    /** @deprecated */
    Route::prefix('assessment-questions')
        ->name('assessmentQuestions.')
        ->controller(AssessmentQuestionController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');

            // Route literal /archive HARUS didaftarkan sebelum /{id},
            // kalau tidak "archive" akan tertangkap oleh {id}.
            Route::get('/archive', 'showArchives')->name('archives');
            Route::patch('/archive', 'restoreArchives')->name('archives.restore')->middleware('admin');
            Route::delete('/archive', 'clearArchives')->name('archives.clear')->middleware('admin');

            Route::post('/', 'store')->name('store')->middleware('admin');
            Route::get('/{id}', 'show')->name('show')->whereNumber('id');
            Route::put('/{id}', 'update')->name('update')->middleware('admin')->whereNumber('id');
            Route::delete('/{id}', 'destroy')->name('destroy')->middleware('admin')->whereNumber('id');
        });

    Route::get('assessment-tracking', [AssessmentTrackingController::class, 'backupIndex']);

    Route::get('self-assessments', [SelfAssessmentController::class, 'index']);
    Route::post('self-assessments', [SelfAssessmentController::class, 'store']);
    Route::get('self-assessments/{selfAssessment}', [SelfAssessmentController::class, 'show']);
    Route::put('self-assessments/{selfAssessment}/answers', [SelfAssessmentController::class, 'saveAnswers']);
    Route::post('self-assessments/{selfAssessment}/questions/{assessmentQuestion}/evidence/{level}', [SelfAssessmentController::class, 'uploadEvidence']);
    Route::delete('self-assessments/{selfAssessment}/questions/{assessmentQuestion}/evidence/{level}', [SelfAssessmentController::class, 'deleteEvidence']);
    Route::post('self-assessments/{selfAssessment}/submit', [SelfAssessmentController::class, 'submit']);

    // Verifikasi berjenjang: ODA (type=on_desk) & OSA (type=on_site).
    Route::get('verifications/{type}', [VerificationController::class, 'index']);
    Route::post('verifications/{type}', [VerificationController::class, 'store']);
    Route::get('verifications/detail/{assessmentVerification}', [VerificationController::class, 'show']);
    Route::put('verifications/detail/{assessmentVerification}/levels', [VerificationController::class, 'saveLevels']);
    Route::post('verifications/detail/{assessmentVerification}/questions/{assessmentQuestion}/evidence/{level}', [VerificationController::class, 'uploadEvidence']);
    Route::delete('verifications/detail/{assessmentVerification}/questions/{assessmentQuestion}/evidence/{level}', [VerificationController::class, 'deleteEvidence']);
    Route::post('verifications/detail/{assessmentVerification}/submit', [VerificationController::class, 'submit']);

    Route::middleware('admin')->group(function () {

        Route::apiResource('roles', RoleController::class);

        Route::apiResource('users', UserController::class);

        Route::apiResource('organization-types', OrganizationTypeController::class)
            ->parameters(['organization-types' => 'organizationType']);

        Route::apiResource('organizations', OrganizationController::class);
        Route::post('organizations/{organization}/move', [OrganizationController::class, 'move']);

        Route::prefix('locations')->group(function () {
            Route::get('/', [LocationController::class, 'index'])->name('locations.index');
            Route::get('{id}', [LocationController::class, 'show'])->name('locations.show');
            Route::post('/', [LocationController::class, 'store'])->name('locations.store');
            Route::put('{id}', [LocationController::class, 'update'])->name('locations.update');
            Route::patch('{id}/activate', [LocationController::class, 'activate'])->name('locations.activate');
            Route::patch('{id}/deactivate', [LocationController::class, 'deactivate'])->name('locations.deactivate');
            Route::delete('{id}', [LocationController::class, 'destroy'])->name('locations.destroy');
        });

    });
});
