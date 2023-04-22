<?php

namespace App\Helpers;

use Carbon\Carbon;

class HelperAPI
{
    public const RESPONSE_STATUS_SUCCESS = 0;
    public const RESPONSE_STATUS_ERROR = 1;
    public const SUB_DAYS_1D = 1;
    public const SUB_DAYS_1K = 7;
    public const SUB_DAYS_2K = 14;
    public const SUB_DAYS_1M = 30;
    public const SUB_DAYS_3M = 90;
    public const SUB_DAYS_6M = 180;

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

    public function getFromDate($paramDate, $defaultDate = self::SUB_DAYS_1D): Carbon
    {
        switch ($paramDate) {
            case '1d':
                $fromDate = Carbon::now()->subDays(self::SUB_DAYS_1D);
                break;
            case '1k':
                $fromDate = Carbon::now()->subDays(self::SUB_DAYS_1K);
                break;
            case '2k':
                $fromDate = Carbon::now()->subDays(self::SUB_DAYS_2K);
                break;
            case '1m':
                $fromDate = Carbon::now()->subDays(self::SUB_DAYS_1M);
                break;
            case '3m':
                $fromDate = Carbon::now()->subDays(self::SUB_DAYS_3M);
                break;
            case '6m':
                $fromDate = Carbon::now()->subDays(self::SUB_DAYS_6M);
                break;
            default:
                $fromDate = $defaultDate;
        }

        return $fromDate;
    }
}
