<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{


    public function index(Request $request)
    {
        $paramDate = $request->get('d');
        $fromDate = null;
        if($paramDate == '1d'){
            $fromDate = Carbon::now()->subDays(1);
        }elseif($paramDate == '1k'){
            $fromDate = Carbon::now()->subDays(7);
        }elseif($paramDate == '2k'){
            $fromDate = Carbon::now()->subDays(14);
        }elseif($paramDate == '1m'){
            $fromDate = Carbon::now()->subDays(30);
        }elseif($paramDate == '3m'){
            $fromDate = Carbon::now()->subDays(60);
        }elseif($paramDate == '6m'){
            $fromDate = Carbon::now()->subDays(180);
        }
        $fromDate = $fromDate ?: Carbon::now()->subDay(30);
        $days = [];
        $labels = [];
        $data = [];
        $now = Carbon::now();
        while ($fromDate < $now) {
            $key = $fromDate->format('Y-m-d');
            $orderList = Order::query()->whereDate('created_at', $key)->get();
            $grouped = $orderList->groupBy('created_by');
            $totalUnPaid = 0;
            $totalPaid = 0;
            $totalCancelled = 0;
            $sumShipped = 0;
            $totalCompleted = 0;
            foreach($orderList as $item){
                 if($item->status == 'unpaid'){
                    $totalUnPaid++;
                 }
                 if($item->status == 'paid'){
                    $totalPaid++;
                 }
                 if($item->status == 'cancelled'){
                    $totalPaid++;
                 }
                 if($item->status == 'shipped'){
                    $sumShipped++;
                 }
                 if($item->status == 'completed'){
                    $totalCompleted++;
                 }
            }
            $data[$key] = [
              'count_customer' =>  $grouped->count(),
              'count_order' => $orderList->count(),
              'unpaid' => $totalUnPaid,
              'paid' => $totalPaid,
              'cancelled' => $totalCancelled,
              'shipped' => $sumShipped,
              'completed' => $totalCompleted,
              'rate_completed' => round(($totalCompleted/$orderList->count())*100) . '%',
            ];
            $fromDate = $fromDate->addDay(1);
        }

        return response($data);
      
    }

    public function OrdersReport(Request $request)
    {
        $paramDate = $request->get('d');
        $fromDate = null;
        if($paramDate == '1d'){
            $fromDate = Carbon::now()->subDays(1);
        }elseif($paramDate == '1k'){
            $fromDate = Carbon::now()->subDays(7);
        }elseif($paramDate == '2k'){
            $fromDate = Carbon::now()->subDays(14);
        }elseif($paramDate == '1m'){
            $fromDate = Carbon::now()->subDays(30);
        }elseif($paramDate == '3m'){
            $fromDate = Carbon::now()->subDays(60);
        }elseif($paramDate == '6m'){
            $fromDate = Carbon::now()->subDays(180);
        }
        $fromDate = $fromDate ? $fromDate : Carbon::now()->subDay(7);
        $days = [];
        $labels = [];
        $now = Carbon::now();
        while ($fromDate < $now) {
            $key = $fromDate->format('Y-m-d');
            $labels[] = $key;
            $count = Order::query()->whereDate('created_at', $key)->count();
            $fromDate = $fromDate->addDay(1);
            $days[] = $count;
        }

        return [
            'labels' => $labels,
            'datasets' => [[
                'label' => 'Orders By Day',
                'backgroundColor' => '#f87979',
                'data' => $days
            ]]
        ];
    }


}
