<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Requests\PostCustomerRequest;
use App\Helpers\HelperAPI;
use Exception;

class CustomerController extends Controller
{
    /**
     * @var Customer
     */
    private $customerModel;

    /**
     * @param Customer $customerModel
     */
    public function __construct(Customer $customerModel)
    {
        $this->customerModel = $customerModel;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function index(Request $request): \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        $customerData = $this->customerModel->getAll($request);
        return response($customerData);
    }

    /**
     * Api update customer data
     *
     * @param PostCustomerRequest $request
     * @return array
     */
    public function update(PostCustomerRequest $request)
    {
        try {
            $customerData = $request->all();
            $customer = $this->customerModel->getCustomerById($request->id);
            $customerData['updated_by'] = $request->user()->id;
            $customerData['status'] = $customerData['status'] ? 'active' : 'disabled';
            foreach (Customer::ADDRESS_TYPE as $type) {
                $addressData = $customerData[$type];
                $addressData['customer_id'] = $customer->user_id;
                $this->customerModel->updateCustomerAddress($addressData);
            }
        } catch (Exception $e) {
            return HelperAPI::responseError($e->getMessage());
        }

        return HelperAPI::responseSuccess($customer->getAttributes(), 'Update Customer success!');
    }

    /**
     * Api delete customer by customer_id
     *
     * @param Request $request
     * @return array
     */
    public function destroy(Request $request): array
    {
        $this->customerModel->deleteById($request->id);
        return HelperAPI::responseSuccess([], 'Delete Customer success!');
    }
}
