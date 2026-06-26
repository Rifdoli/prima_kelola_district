<?php

namespace App\Traits;

trait ApiResponse
{
    protected function success($data = null, string $message = 'OK', int $status = 200)
    {
        return response()->json([
            'data' => $data,
            'message' => $message,
        ], $status);
    }

    protected function error(string $message, int $status = 400)
    {
        return response()->json([
            'data' => null,
            'message' => $message,
        ], $status);
    }
}
