<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Helpers\HelperAPI;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * @param Request $request
     * @return array
     */
    public function index(Request $request): array
    {
        $fromDate = HelperAPI::getFromDate($request->get('d'), HelperAPI::SUB_DAYS_1M);
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
                $fromDate = $fromDate->addDay(HelperAPI::SUB_DAYS_1D);
            }

            return HelperAPI::responseSuccess($data);
        } catch (\Throwable $e) {
            return HelperAPI::responseError($e->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return array
     */
    public function OrdersReport(Request $request): array
    {
        try {
            $fromDate = HelperAPI::getFromDate($request->get('d'), HelperAPI::SUB_DAYS_1K);
            $days = [];
            $labels = [];
            while ($fromDate < Carbon::now()) {
                $key = $fromDate->format('Y-m-d');
                $labels[] = $key;
                $count = Order::query()->whereDate('created_at', $key)->count();
                $fromDate = $fromDate->addDay(HelperAPI::SUB_DAYS_1D);
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

            return HelperAPI::responseSuccess($data);
        } catch (\Throwable $e) {
            return HelperAPI::responseError($e->getMessage());
        }
    }
}
