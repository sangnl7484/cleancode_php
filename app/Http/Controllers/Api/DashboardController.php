<?php

namespace App\Http\Controllers\Api;

use App\Helpers\HelperAPI;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;

class DashboardController extends Controller
{
    protected $order;

    protected $customer;

    public function __construct(
        Order $order,
        Customer $customer
    )
    {
        $this->order = $order;
        $this->customer = $customer;
    }

    /**
     * Count
     *
     * @param Request $request
     * @return array|Application|ResponseFactory|Response
     */
    public function count(Request $request): Response|array|Application|ResponseFactory
    {
        try {
            $fromDate = HelperAPI::getFromDate($request->get('d'));

            $activeCustomers = Customer::where('status', 'active')->get()->count();
            $activeProducts = Product::where('published', '=', 1)->get()->count();

            $query = Order::where('status', 'paid');

            if ($fromDate) {
                $query->where('created_at', '>', $fromDate);
            }

            $paidOrders = $query->count();
            $totalIncome = round($query->sum('total_price'));
            $ordersByCountry =  $this->order->getOrdersByCountry($fromDate);
            $latestCustomers =  $this->customer->getLatestCustomers();
            $latestOrders = $this->order->getLatestOrders();

            return response(compact('activeCustomers', 'activeProducts', 'paidOrders', 'totalIncome', 'ordersByCountry', 'latestCustomers', 'latestOrders'));
        } catch (\Exception $e) {
            return HelperAPI::responseError($e->getMessage());
        }
    }

    /**
     * Top User Order
     *
     * @param Request $request
     * @return array|Application|ResponseFactory|Response
     */
    public function TopUserOrders(Request $request): Response|array|Application|ResponseFactory
    {
        try {
            $getallUsers = Customer::where('status', 'active')->get();
            $topUserOrder = [];
            $fromDate = HelperAPI::getFromDate($request->get('d'));

            foreach ($getallUsers as $key => $value) {
             $query = Order::where('status', 'completed')->where('created_by', $value->user_id);

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
        } catch (\Exception $e) {
            return HelperAPI::responseError($e->getMessage());
        }
    }

    /**
     * Top Sellers
     *
     * @param Request $request
     * @return array|Application|ResponseFactory|Response
     */
    public function topSellers(Request $request): Response|array|Application|ResponseFactory
    {
        try {
            $fromDate = HelperAPI::getFromDate($request->get('d'));

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
        } catch (\Exception $e) {
            return HelperAPI::responseError($e->getMessage());
        }
    }

}
