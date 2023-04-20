<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class CustomerController extends Controller
{

    public function index(Request $request)
    {
        $search = $request->search;
        $sortField = $request->sort_field;
        $sortDirection = $request->sort_direction;
        $query = Customer::query()
            ->orderBy("customers.$sortField", $sortDirection);
        if ($search) {
            $query->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%")->join('users', 'customers.user_id', '=', 'users.id')->orWhere('users.email', 'like', "%{$search}%")->orWhere('customers.phone', 'like', "%{$search}%");
        }
        $data = $query->get();
        $results = array();
        foreach ($data as $item) {
            $results[] = [
                'id' => $item->user_id,
                'first_name' => $item->first_name,
                'last_name' => $item->last_name,
                'email' => $item->user->email,
                'phone' => $item->phone,
                'zipcode' => DB::table('customer_addresses')->select('country_code')->where('customer_id', $item->id)->first()?->zipcode,
                'order_total' => $item->user->orders->where('status', 2)->count(), // status = 2 is complete
                'status' => $item->status,
                'created_at' => (new \DateTime($item->created_at))->format('Y-m-d H:i:s'),
                'updated_at' => (new \DateTime($item->updated_at))->format('Y-m-d H:i:s'),
            ];
        }

        return response($data);
    }

    public function update(Request $request)
    {
        $customerData = $request->all();
        $idCustomer = $request->id;
        $customer = Customer::find($idCustomer);
        $customerData['updated_by'] = $request->user()->id;
        $customerData['status'] = $customerData['status'] ? 'active' : 'disabled';
        $shippingData = $customerData['shippingAddress'];
        $billingData = $customerData['billingAddress'];
        if ($customer->update($customerData)) {
            if ($customer->shippingAddress) {
                if ($customer->shippingAddress->update($shippingData)) {
                    if ($customer->billingAddress) {
                        $customer->billingAddress->update($billingData);
                    } else {
                        $billingData['customer_id'] = $customer->user_id;
                        $billingData['type'] = 'billing';
                        CustomerAddress::create($billingData);
                    }
                }
            } else {
                $shippingData['customer_id'] = $customer->user_id;
                $shippingData['type'] = 'shipping';
                if (CustomerAddress::create($shippingData)) {
                    if ($customer->billingAddress) {
                        $customer->billingAddress->update($billingData);
                    } else {
                        $billingData['customer_id'] = $customer->user_id;
                        $billingData['type'] = 'billing';
                        CustomerAddress::create($billingData);
                    }
                }
            }
        }
        return Response::json($customer);
    }


    public function destroy(Request $request)
    {
        $id = $request->id;
        $customer = Customer::find($id)->delete();
        return response()->noContent();
    }
}
