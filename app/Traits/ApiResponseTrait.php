<?php

namespace App\Traits;

trait ApiResponseTrait
{
    public function successResponse($message = 'Success', $data = [], $status = 200)
    {
        return response()->json([
            'success' => true,
            'statusCode' => $status,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    public function errorResponse($message = 'Something went wrong', $errors = [], $status = 400)
    {
        return response()->json([
            'success' => false,
            'statusCode' => $status,
            'message' => $message,
            'errors' => $errors
        ], $status);
    }
}
