<?php

namespace App\Helpers;

class HelperAPI
{
    public const RESPONSE_STATUS_SUCCESS = 0;
    public const RESPONSE_STATUS_ERROR = 1;

    public static function responseError(string $message = 'Error'): array
    {
        return [
            'status' => self::RESPONSE_STATUS_ERROR,
            'message' => $message
        ];
    }

    public static function responseSuccess(array $data = [], string $message = 'Success'): array
    {
        return [
            'status' => self::RESPONSE_STATUS_SUCCESS,
            'message' => $message,
            'data' => $data
        ];
    }

    public static function formatDateTime(string $dateTime): string
    {
        return (new \DateTime($dateTime))->format('Y-m-d H:i:s');
    }
}
