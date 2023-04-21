<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Mail\OrderUpdateEmail;
use App\Models\Api\Product;
use App\Models\Api\User;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderItem;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{

    public function orderDetail(Request $request)
    {
        $order = Order::find($request->id);
        $user = User::find($order->created_by);
        $data = [];
        $data['id'] = $order->id;
        $data['status'] = $order->status;
        $data['total_price'] = $order->total_price;
        $data['created_at'] = (new \DateTime($order->created_at))->format('Y-m-d H:i:s');
        $data['updated_at'] = (new \DateTime($order->updated_at))->format('Y-m-d H:i:s');
        $data['list_status'] =  ['unpaid', 'paid', 'cancelled', 'shipped', 'completed'];
        $items = [];

        foreach (OrderItem::where('order_id', $order->id)->get() as $item) {
            $product = Product::find($item->product_id);
            $items[] = [
                'id' => $item->id,
                'unit_price' => $item->unit_price,
                'quantity' => $item->quantity,
                'product' => [
                    'id' => $product->id,
                    'slug' => $product->slug,
                    'title' => $product->title,
                    'image' => $product->image,
                ]
            ];
        }
        $data['items'] = $items;

        $customer = [
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'last_name' => $user->last_name,
            'phone' => $user->phone,
        ];
        $shippingAddress = CustomerAddress::where('type', '=', 'shipping')->where('customer_id', $user->id)->get()->toArray();
        $billingAddress = CustomerAddress::where('type', '=', 'billing')->where('customer_id', $user->id)->get()->toArray();
        $customer['shippingAddress'] = $shippingAddress;
        $customer['billingAddress'] = $billingAddress;
        $data['customer'] = $customer;
        return response($data);
    }

    public function changeStatusOrder(Request $request)
    {
        $id = $request->id;
        $order = Order::find($id);
        $order->status = $request->status;
        $order->save();
        Mail::to($order->user)->send(new OrderUpdateEmail($order));
        return response('', 200);
    }

    public function createOrder(Request $request)
    {
        try {
            $orderItems = [];
            $totalPrice = 0;
            $orderDetail = $request->input('orderDetail');
            $products = $request->input('products');
            $cartItems = $request->input('cartItems');
            $checkQuantityProduct = true;

            foreach ($products as $product) {
                $quantity = $cartItems[$product['id']]['quantity'];
                $currentQuantity = DB::table('products')->where('id', $product['id'])->first('quantity')?->quantity;
                if ($quantity > $currentQuantity) {
                    $checkQuantityProduct = false;
                } else {
                    $totalPrice += $product['price'] * $quantity;
                    $orderItems[] = [
                        'product_id' => $product['id'],
                        'quantity' => $quantity,
                        'unit_price' => $product['price']
                    ];
                }
            }

            if ($checkQuantityProduct) {
                return response('Not enough product', 400);
            } else {

                $orderData = [
                    'total_price' => $totalPrice,
                    'status' => 'unpaid',
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ];

                if ($order = Order::create($orderData)) {

                    $orderDetail['order_id'] = $order->id;
                    if (OrderDetail::create($orderDetail)) {

                        foreach ($orderItems as $orderItem) {
                            $orderItem['order_id'] = $order->id;
                            if (!OrderItem::create($orderItem)) {
                                DB::table('products')->where('id', $orderItem['product_id'])->update(['quantity', ($currentQuantity - $quantity)]);
                                return response(false, 400);
                            }
                        }
                        return response(true, 200);
                    } else {
                        return response(false, 400);
                    }
                } else {
                    return response(false, 400);
                }
            }
        } catch (Exception $e) {
            return response(false, 400);
        }
    }

    public function getOrderList(Request $request)
    {
        $status = $request->status;
        $search = $request->search;

        $query = Order::query();
        if ($status) {
            $query->where('status', $status);
            if ($search) {
                $query->whereHas('user', function ($query) use ($search) {
                    $query->where('name', 'like',  '%' . $search . '%');
                });
            }
        } else {
            $query->where('status', $status);
            if ($search) {
                $query->whereHas('user', function ($query) use ($search) {
                    $query->where('name', 'like',  '%' . $search . '%');
                });
            }
        }
        return response($query->get());
    }
}
