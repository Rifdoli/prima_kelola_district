<?php

use App\Http\Controllers\Api\AssessmentQuestionController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\OrganizationTypeController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SelfAssessmentController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('assessment-questions', [AssessmentQuestionController::class, 'index']);

    Route::get('self-assessments', [SelfAssessmentController::class, 'index']);
    Route::post('self-assessments', [SelfAssessmentController::class, 'store']);
    Route::get('self-assessments/{selfAssessment}', [SelfAssessmentController::class, 'show']);
    Route::put('self-assessments/{selfAssessment}/answers', [SelfAssessmentController::class, 'saveAnswers']);
    Route::post('self-assessments/{selfAssessment}/questions/{assessmentQuestion}/evidence', [SelfAssessmentController::class, 'uploadEvidence']);
    Route::post('self-assessments/{selfAssessment}/submit', [SelfAssessmentController::class, 'submit']);

    Route::middleware('admin')->group(function () {
        Route::apiResource('roles', RoleController::class);
        Route::apiResource('users', UserController::class);
        Route::apiResource('organization-types', OrganizationTypeController::class)
            ->parameters(['organization-types' => 'organizationType']);
        Route::apiResource('organizations', OrganizationController::class);
        Route::post('organizations/{organization}/move', [OrganizationController::class, 'move']);
    });
});
