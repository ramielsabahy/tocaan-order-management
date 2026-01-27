<?php

namespace App\Providers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Illuminate\Support\ServiceProvider;

class ApiResponseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        ResponseFacade::macro('success', function (mixed $data = null): JsonResponse {
            return response()->json([
                'message' => __('messages.success'),
                'data' => $data,
            ], Response::HTTP_OK);
        });

        ResponseFacade::macro('successWithMessage', function (string $message, mixed $data = null): JsonResponse {
            return response()->json([
                'message' => $message,
                'data' => $data,
            ], Response::HTTP_OK);
        });

        ResponseFacade::macro('created', function (?string $message = null, mixed $data = null): JsonResponse {
            return response()->json([
                'message' => $message ?? __('messages.created'),
                'data' => $data,
            ], Response::HTTP_CREATED);
        });

        ResponseFacade::macro('failedValidation', function (?string $message = null, mixed $data = null): JsonResponse {
            return response()->json([
                'message' => $message ?? __('messages.Failed validation'),
                'data' => $data,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        });
    }
}
