<?php

namespace App\Http\Controllers\Api;

use App\Helpers\HelperAPI;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;

class DashboardController extends Controller
{
    public const CUSTOMER_STATUS_ACTIVE = 'active';
    public const PRODUCT_PUBLISHED = 1;
    public const TOP_USER_ORDER = 5;
    protected Order $order;
    protected Customer $customer;

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

            $activeCustomers = Customer::where('status', self::CUSTOMER_STATUS_ACTIVE)->get()->count();
            $activeProducts = Product::where('published', '=', self::PRODUCT_PUBLISHED)->get()->count();

            $query = Order::where('status', 'paid');

            if ($fromDate) {
                $query->where('created_at', '>', $fromDate);
            }

            $paidOrders = $query->count();
            $totalIncome = round($query->sum('total_price'));
            $ordersByCountry = $this->order->getOrdersByCountry($fromDate);
            $latestCustomers = $this->customer->getLatestCustomers();
            $latestOrders = $this->order->getLatestOrders();

            return HelperAPI::responseSuccess(compact(
                    'activeCustomers',
                    'activeProducts',
                    'paidOrders',
                    'totalIncome',
                    'ordersByCountry',
                    'latestCustomers',
                    'latestOrders')
            );
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
            $getAllUsers = Customer::where('status', self::CUSTOMER_STATUS_ACTIVE)->get();
            $topUserOrder = [];
            $fromDate = HelperAPI::getFromDate($request->get('d'));

            foreach ($getAllUsers as $value) {
                $query = Order::where('status', 'completed')->where('created_by', $value->user_id);
                if ($fromDate) {
                    $query->where('orders.created_at', '>', $fromDate);
                }

                $topUserOrder[] = [
                    'user_id ' => $value->user_id,
                    'user_name' => $value->first_name . ' ' . $value->last_name,
                    'countOrder' => $query->count(),
                ];
            }

            $topUserOrder = collect($topUserOrder)->sortByDesc('countOrder')->slice(0, self::TOP_USER_ORDER)->toArray();
            return HelperAPI::responseSuccess($topUserOrder);
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

                $getProduct = Product::where('id', $key)->where('published', self::PRODUCT_PUBLISHED)->whereNull('deleted_at')->first();
                if ($getProduct) {
                    $topSellers[] = [
                        'product_id' => $key,
                        'product_name' => $getProduct->title,
                        'sellers' => $value,
                    ];
                }
            }

            return HelperAPI::responseSuccess($topSellers);
        } catch (\Exception $e) {
            return HelperAPI::responseError($e->getMessage());
        }
    }
}
