<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssessmentQuestion;
use App\Traits\ApiResponse;

class AssessmentQuestionController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->success(AssessmentQuestion::orderBy('sort_order')->get());
    }
}
