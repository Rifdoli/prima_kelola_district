<?php

namespace App\Traits;

trait ApiResponse
{
    protected function success($data = null, string $message = 'OK', int $status = 200)
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    protected function error(string $message, int $status = 400)
    {
        return response()->json([
            'message' => $message,
            'data' => null,
        ], $status);
    }
}
