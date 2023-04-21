<?php

namespace App\Helpers;

use Carbon\Carbon;

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

    public static function getFromDate($paramDate): ?Carbon
    {
        $fromDate = null;
        switch ($paramDate) {
            case '1d':
                $fromDate = Carbon::now()->subDays(1);
                break;
            case '1k':
                $fromDate = Carbon::now()->subDays(7);
                break;
            case '2k':
                $fromDate = Carbon::now()->subDays(14);
                break;
            case '1m':
                $fromDate = Carbon::now()->subDays(30);
                break;
            case '3m':
                $fromDate = Carbon::now()->subDays(60);
                break;
            case '6m':
                $fromDate = Carbon::now()->subDays(180);
                break;
        }
        return $fromDate;
    }
}
