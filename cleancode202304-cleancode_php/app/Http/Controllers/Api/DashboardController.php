<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function count(Request $request)
    {
        $paramDate = $request->get('d');
        $fromDate = null;
        if ($paramDate == '1d') {
            $fromDate = Carbon::now()->subDays(1);
        } elseif ($paramDate == '1k') {
            $fromDate = Carbon::now()->subDays(7);
        } elseif ($paramDate == '2k') {
            $fromDate = Carbon::now()->subDays(14);
        } elseif ($paramDate == '1m') {
            $fromDate = Carbon::now()->subDays(30);
        } elseif ($paramDate == '3m') {
            $fromDate = Carbon::now()->subDays(60);
        } elseif ($paramDate == '6m') {
            $fromDate = Carbon::now()->subDays(180);
        }

        $activeCustomers = Customer::where('status', 'active')->get()->count();

        $activeProducts = Product::where('published', '=', 1)->get()->count();


        $query = Order::query()->where('status', 'paid');

        if ($fromDate) {
            $query->where('created_at', '>', $fromDate);
        }

        $paidOrders = $query->count();

        $totalIncome = round($query->sum('total_price'));


        $query = Order::query()->select(['c.name', DB::raw('count(orders.id) as count')])->join('users', 'created_by', '=', 'users.id')->join('customer_addresses AS a', 'users.id', '=', 'a.customer_id')->join('countries AS c', 'a.country_code', '=', 'c.code')->where('status', 'paid')->where('a.type', 'billing')->groupBy('c.name');

        if ($fromDate) {
            $query->where('orders.created_at', '>', $fromDate);
        }

        $ordersByCountry =  $query->get();

        $latestCustomers =  Customer::query()->select(['id', 'first_name', 'last_name', 'u.email', 'phone', 'u.created_at'])->join('users AS u', 'u.id', '=', 'customers.user_id')->where('status', 'active')->orderBy('created_at', 'desc')->limit(5)->get();

        $latestOrders = Order::query()->select(['o.id', 'o.total_price', 'o.created_at', DB::raw('COUNT(oi.id) AS items'),    'c.user_id', 'c.first_name', 'c.last_name'])->from('orders AS o')->join('order_items AS oi', 'oi.order_id', '=', 'o.id')->join('customers AS c', 'c.user_id', '=', 'o.created_by')->where('o.status', 'paid')->limit(10)->orderBy('o.created_at', 'desc')->groupBy('o.id', 'o.total_price', 'o.created_at', 'c.user_id', 'c.first_name', 'c.last_name')->get();

        return response(compact('activeCustomers', 'activeProducts', 'paidOrders', 'totalIncome', 'ordersByCountry', 'latestCustomers', 'latestOrders'));
    }

    
    public function TopUserOrders(Request $request)
    {
        $getallUsers = Customer::where('status', 'active')->get();
        $topUserOrder = [];
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
        foreach ($getallUsers as $key => $value) {
         $query = Order::query()->where('status', 'completed')->where('created_by', $value->user_id);
         
        if ($fromDate) {
            $query->where('orders.created_at', '>', $fromDate);
        }

         $topUserOrder[] = [
             'user_id '=> $value->user_id,
             'user_name'=> $value->first_name.' '.$value->last_name,
             'countOrder'=> $query->count(),
         ];
         }
   
       $topUserOrder = collect($topUserOrder)->sortByDesc('countOrder')->slice(0, 5)->toArray();
       return response($topUserOrder);
    }

    public function topSellers(Request $request)
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
        $topSellers = [];
        $getAllOrderProduct = OrderItem::all();
        $productsTopSellersIds = [];
        foreach ($getAllOrderProduct as $key => $value) {
            $query = Order::query()->where('id', $value->order_id)->where('status', 'completed');
            if ($fromDate) {
                $query->where('orders.created_at', '>', $fromDate);
            }
            if ($query->first()) {
                $product_id = $value->product_id;
                if (isset($productsTopSellersIds[$product_id])) {
                    $productsTopSellersIds[$product_id] += $value->quantity;
                } else {
                    $productsTopSellersIds[$product_id] = $value->quantity;
                }
            }
        }

        arsort($productsTopSellersIds);

        foreach ($productsTopSellersIds as $key => $value) {
            if (count($topSellers) == 5) {
                break;
            }

            $getproduct = Product::where('id', $key)->where('published', 1)->whereNull('deleted_at')->first();
            if ($getproduct) {
                $topSellers[] = [
                    'product_id' => $key,
                    'product_name' => $getproduct->title,
                    'sellers' => $value,
                ];
            }
        }

        return response($topSellers);
    }
}
