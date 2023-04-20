<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Customer extends Model
{
    use HasFactory;

    const LIMIT_SQL = 12;

    /**
     * @var string[]
     */
    const ADDRESS_TYPE = ['shippingAddress', 'billingAddress'];

    /**
     * @var string
     */
    protected $primaryKey = 'user_id';

    /**
     * @var string[]
     */
    protected $fillable = ['first_name', 'last_name', 'phone', 'status',];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasOne
     */
    private function _getAddresses(): HasOne
    {
        return $this->hasOne(CustomerAddress::class, 'customer_id', 'user_id');
    }

    /**
     * @return HasOne
     */
    public function shippingAddress(): HasOne
    {
        return $this->_getAddresses()->where('type', '=', 'shipping');
    }

    /**
     * @return HasOne
     */
    public function billingAddress(): HasOne
    {
        return $this->_getAddresses()->where('type', '=', 'billing');
    }

    /**
     * Scope a query to sort.
     *
     * @param Builder $query
     * @param Request $request
     * @return Builder
     */
    public function scopeSort(Builder $query, Request $request): Builder
    {
        if ($request->has('sort_field') && $request->has('sort_direction')) {
            $sort = $request->get('sort_field');
            $directory = $request->get('sort_direction');
            if (Str::contains($sort, '-')) {
                $sort = Str::remove('-', $sort);
                $directory = 'ASC';
            }
            $query->orderBy($sort, $directory);
        }
        return $query;
    }

    /**
     * Scope a query to filter.
     *
     * @param Builder $query
     * @param Request $request
     * @return Builder
     */
    public function scopeFilter(Builder $query, Request $request): Builder
    {
        if ($request->has('filter')) {
            //todo
        }
        return $query;
    }

    /**
     * Scope a query to search.
     *
     * @param Builder $query
     * @param Request $request
     * @return Builder
     */
    public function scopeSearch(Builder $query, Request $request): Builder
    {
        if ($request->has('search') && strlen($request->search) > 0) {
            $search = $request->search;
            $query->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%")
                ->join('users', 'customers.user_id', '=', 'users.id')
                ->orWhere('users.email', 'like', "%{$search}%")
                ->orWhere('customers.phone', 'like', "%{$search}%");
        }
        return $query;
    }

    /**
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function getAll(Request $request): LengthAwarePaginator
    {
        $limit = $request->has('limit') ?? self::LIMIT_SQL;
        return $this
            ->sort($request)
            ->filter($request)
            ->search($request)
            ->paginate($limit);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getCustomerById($id)
    {
        return $this::find($id);
    }

    /**
     * @param $addressData
     * @return void
     */
    public function updateCustomerAddress($addressData): void
    {
        CustomerAddress::updateOrCreate(
            ['customer_id' => $addressData['customer_id'], 'type' => $addressData['type']],
            $addressData
        );
    }

    /**
     * @param $id
     * @return void
     */
    public function deleteById($id): void
    {
        $customer = $this->getCustomerById($id);
        if ($customer) {
            DB::table('customer_addresses')->where('customer_id', $id)->delete();
            $customer->delete();
        }
    }
}
