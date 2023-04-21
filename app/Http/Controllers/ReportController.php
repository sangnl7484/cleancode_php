<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Helpers\HelperAPI;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public const SUB_DAYS_1D = 1;
    public const SUB_DAYS_1K = 7;
    public const SUB_DAYS_2K = 14;
    public const SUB_DAYS_1M = 30;
    public const SUB_DAYS_3M = 60;
    public const SUB_DAYS_6M = 180;

    /**
     * @param Request $request
     * @return array
     */
    public function index(Request $request): array
    {
        $fromDate = $this->getFromDate($request->get('d'), self::SUB_DAYS_1M);
        try {
            $data = [];

            while ($fromDate < Carbon::now()) {
                $key = $fromDate->format('Y-m-d');
                $orderList = Order::query()->whereDate('created_at', $key)->get();
                $grouped = $orderList->groupBy('created_by');
                $totalUnPaid = 0;
                $totalPaid = 0;
                $totalCancelled = 0;
                $sumShipped = 0;
                $totalCompleted = 0;
                foreach ($orderList as $item) {
                    switch ($item->status) {
                        case "unpaid":
                            $totalUnPaid++;
                            break;
                        case "paid":
                            $totalPaid++;
                            break;
                        case "cancelled":
                            $totalCancelled++;
                            break;
                        case "shipped":
                            $sumShipped++;
                            break;
                        case "completed":
                            $totalCompleted++;
                            break;
                        default:
                            break;
                    }
                }

                $data[$key] = [
                    'count_customer' => $grouped->count(),
                    'count_order' => $orderList->count(),
                    'unpaid' => $totalUnPaid,
                    'paid' => $totalPaid,
                    'cancelled' => $totalCancelled,
                    'shipped' => $sumShipped,
                    'completed' => $totalCompleted,
                    'rate_completed' => round(($totalCompleted / $orderList->count()) * 100) . '%',
                ];
                $fromDate = $fromDate->addDay(self::SUB_DAYS_1D);
            }
        } catch (\Throwable $e) {
            return HelperAPI::responseError($e->getMessage());
        }

        return HelperAPI::responseSuccess($data);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function OrdersReport(Request $request): array
    {
        try {
            $fromDate = $this->getFromDate($request->get('d'), self::SUB_DAYS_1K);
            $days = [];
            $labels = [];
            while ($fromDate < Carbon::now()) {
                $key = $fromDate->format('Y-m-d');
                $labels[] = $key;
                $count = Order::query()->whereDate('created_at', $key)->count();
                $fromDate = $fromDate->addDay(self::SUB_DAYS_1D);
                $days[] = $count;
            }
            $data = [
                'labels' => $labels,
                'datasets' => [[
                    'label' => 'Orders By Day',
                    'backgroundColor' => '#f87979',
                    'data' => $days
                ]]
            ];
        } catch (\Throwable $e) {
            return HelperAPI::responseError($e->getMessage());
        }

        return HelperAPI::responseSuccess($data);
    }

    /**
     * @param $paramDate
     * @param $defaultDate
     * @return Carbon
     */
    public function getFromDate($paramDate, $defaultDate): Carbon
    {
        switch ($paramDate) {
            case "1d":
                $fromDate = Carbon::now()->subDays(self::SUB_DAYS_1D);
                break;
            case "1k":
                $fromDate = Carbon::now()->subDays(self::SUB_DAYS_1K);
                break;
            case "2k":
                $fromDate = Carbon::now()->subDays(self::SUB_DAYS_2K);
                break;
            case "1m":
                $fromDate = Carbon::now()->subDays(self::SUB_DAYS_1M);
                break;
            case "3m":
                $fromDate = Carbon::now()->subDays(self::SUB_DAYS_3M);
                break;
            case "6m":
                $fromDate = Carbon::now()->subDays(self::SUB_DAYS_6M);
                break;
            default:
                $fromDate = $defaultDate;
        }

        return $fromDate;
    }
}
