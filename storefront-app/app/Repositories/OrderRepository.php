<?php
namespace App\Repositories;

use App\Enums\DeliveryStatus;
use App\Exceptions\InvalidOrderDataException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrderRepository {
    protected const string ORDER_TABLE = 'app_order';
    protected const string ITEM_TABLE = 'ordered_cart';

    # $items: [['sku' => int, 'item_count' => int, 'item_price' => int], ...]
    # the total is computed here from the items so it can't disagree with them
    public function insert_order(int $user_id, array $items,
        string $street_name, string $street_num, ?string $apt_number,
        string $city, string $state, string $postal_code):int{
        if (count($items) === 0) {
            throw new InvalidOrderDataException('An order must contain at least one item.');
        }
        $total_price = 0;
        foreach ($items as $item) {
            $total_price += $item['item_count'] * $item['item_price'];
        }
        try {
            return DB::transaction(function () use ($user_id, $items, $total_price,
                $street_name, $street_num, $apt_number, $city, $state, $postal_code) {
                $order_id = DB::table(static::ORDER_TABLE)->insertGetId([
                    'user_id' => $user_id,
                    'total_price' => $total_price,
                    'street_name' => $street_name,
                    'street_num' => $street_num,
                    'apt_number' => $apt_number,
                    'city' => $city,
                    'state' => $state,
                    'postal_code' => $postal_code
                ], 'order_id');
                $rows = [];
                foreach ($items as $item) {
                    $rows[] = [
                        'order_id' => $order_id,
                        'sku' => $item['sku'],
                        'item_count' => $item['item_count'],
                        'item_price' => $item['item_price']
                    ];
                }
                DB::table(static::ITEM_TABLE)->insert($rows);
                return $order_id;
            });
        } catch (QueryException $e) {
            if ($this->is_check_violation($e)) {
                throw new InvalidOrderDataException('Prices and quantities must be positive.', 0, $e);
            }
            if ($this->is_unique_violation($e)) {
                throw new InvalidOrderDataException('Each product may appear only once per order.', 0, $e);
            }
            throw $e;
        }
    }

    # customer side get order
    public function get_user_order(int $order_id, int $user_id):?object{
        return DB::table(static::ORDER_TABLE)
            ->where('order_id', $order_id)
            ->where('user_id', $user_id)
            ->first();
    }

    # for admin only
    public function get_order_by_id(int $order_id):?object{
        return DB::table(static::ORDER_TABLE)
            ->where('order_id', $order_id)->first();
    }

    public function get_orders_by_user_id(int $user_id):Collection{
        return DB::table(static::ORDER_TABLE)
            ->where('user_id', $user_id)
            ->orderByDesc('created_at')
            ->get();
    }

    public function get_orders_by_status(DeliveryStatus $status):Collection{
        return DB::table(static::ORDER_TABLE)
            ->where('status', $status->value)
            ->orderBy('created_at')
            ->get();
    }

    # admin order list || left join so orders from deleted customers still show
    public function get_all_orders():Collection{
        return DB::table(static::ORDER_TABLE)
            ->leftJoin('customer', 'customer.user_id', '=', 'app_order.user_id')
            ->orderByDesc('app_order.created_at')
            ->get(['app_order.*', 'customer.email']);
    }

    # item_price is the price paid at purchase
    public function get_order_items(int $order_id):Collection{
        return DB::table(static::ITEM_TABLE)
            ->join('product', 'product.sku', '=', 'ordered_cart.sku')
            ->where('ordered_cart.order_id', $order_id)
            ->orderBy('ordered_cart.sku')
            ->get([
                'ordered_cart.sku', 'ordered_cart.item_count',
                'ordered_cart.item_price', 'product.product_name',
                'product.img_url'
            ]);
    }

    public function update_order_status(int $order_id, DeliveryStatus $status):bool{
        return DB::table(static::ORDER_TABLE)
            ->where('order_id', $order_id)
            ->update([
                'status' => $status->value,
                'delivered_at' => $status === DeliveryStatus::DELIVERED
                    ? DB::raw('NOW()') : null
            ]) > 0;
    }

    public function update_shipment_id(int $order_id, string $shipment_id):bool{
        return DB::table(static::ORDER_TABLE)
            ->where('order_id', $order_id)
            ->update(['shipment_id' => $shipment_id]) > 0;
    }

    # only PENDING/PROCESSING orders can be canceled; 
    # pass $user_id for customer requests (default null for admin)
    # returns false if the order doesn't exist, isn't customer's order, or already shipped/canceled
    public function cancel_order(int $order_id, ?int $user_id = null):bool{
        return DB::table(static::ORDER_TABLE)
            ->where('order_id', $order_id)
            ->whereIn('status', [
                DeliveryStatus::PENDING->value,
                DeliveryStatus::PROCESSING->value
            ])
            ->when($user_id !== null, fn($q) => $q->where('user_id', $user_id))
            ->update([
                'status' => DeliveryStatus::CANCELED->value,
                'delivered_at' => null
            ]) > 0;
    }

    protected function is_check_violation(QueryException $e):bool{
        return ($e->errorInfo[0] ?? null) === '23514';
    }

    protected function is_unique_violation(QueryException $e):bool{
        return ($e->errorInfo[0] ?? null) === '23505';
    }
}