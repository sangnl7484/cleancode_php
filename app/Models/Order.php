<?php

namespace App\Models;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['status', 'total_price', 'created_by', 'updated_by'];

    public function isPaid()
    {
        return $this->status === 'paid';
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getOrdersByCountry($fromDate){
        $order = $this::query()->select(['c.name', DB::raw('count(orders.id) as count')])
            ->join('users', 'created_by', '=', 'users.id')
            ->join('customer_addresses AS a', 'users.id', '=', 'a.customer_id')
            ->join('countries AS c', 'a.country_code', '=', 'c.code')
            ->where('status', 'paid')
            ->where('a.type', 'billing')
            ->groupBy('c.name');
        if ($fromDate) {
            $order->where('orders.created_at', '>', $fromDate);
        }
        return $order->get();
    }

    public function getLatestOrders(){
        return $this::query()->select(['o.id', 'o.total_price', 'o.created_at', DB::raw('COUNT(oi.id) AS items'), 'c.user_id', 'c.first_name', 'c.last_name'])
            ->from('orders AS o')
            ->join('order_items AS oi', 'oi.order_id', '=', 'o.id')
            ->join('customers AS c', 'c.user_id', '=', 'o.created_by')
            ->where('o.status', 'paid')->limit(10)
            ->orderBy('o.created_at', 'desc')
            ->groupBy('o.id', 'o.total_price', 'o.created_at', 'c.user_id', 'c.first_name', 'c.last_name')
            ->get();
    }
}
