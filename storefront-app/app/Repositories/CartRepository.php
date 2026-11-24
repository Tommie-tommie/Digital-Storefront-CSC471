<?php
namespace App\Repositories;

use App\Exceptions\InvalidCartDataException;
use App\Exceptions\ProductNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CartRepository {
    protected const string CART_TABLE = 'cart';
    protected const string ITEM_TABLE = 'in_cart';

    # creates the cart if the user doesn't have one yet, then returns its id
    public function get_or_create_cart_id(int $user_id):int{
        DB::table(static::CART_TABLE)->insertOrIgnore(['user_id' => $user_id]);
        return (int) DB::table(static::CART_TABLE)
            ->where('user_id', $user_id)->value('cart_id');
    }

    public function get_cart_id_by_user_id(int $user_id):?int{
        $cart = DB::table(static::CART_TABLE)
            ->where('user_id', $user_id)->first(['cart_id']);
        return $cart? (int) $cart->cart_id: null;
    }

    # adds to the existing count if the product is already in the cart
    public function add_item(int $cart_id, int $sku, int $quantity):void{
        try {
            DB::table(static::ITEM_TABLE)->upsert(
                [['cart_id' => $cart_id, 'sku' => $sku, 'item_count' => $quantity]],
                ['cart_id', 'sku'],
                ['item_count' => DB::raw('in_cart.item_count + excluded.item_count')]
            );
        } catch (QueryException $e) {
            if ($this->is_foreign_key_violation($e)) {
                throw new ProductNotFoundException('Product does not exist.', 0, $e);
            }
            if ($this->is_check_violation($e)) {
                throw new InvalidCartDataException('Quantity must be at least 1.', 0, $e);
            }
            throw $e;
        }
    }

    # sets an exact quantity || 0 or less removes the item
    public function set_item_count(int $cart_id, int $sku, int $quantity):bool{
        if ($quantity <= 0) {
            return $this->remove_item($cart_id, $sku);
        }
        return DB::table(static::ITEM_TABLE)
            ->where('cart_id', $cart_id)
            ->where('sku', $sku)
            ->update(['item_count' => $quantity]) > 0;
    }

    public function remove_item(int $cart_id, int $sku):bool{
        return DB::table(static::ITEM_TABLE)
            ->where('cart_id', $cart_id)
            ->where('sku', $sku)
            ->delete() > 0;
    }

    # joins product so the cart always shows current price, name, and stock
    public function get_cart_items(int $cart_id):Collection{
        return DB::table(static::ITEM_TABLE)
            ->join('product', 'product.sku', '=', 'in_cart.sku')
            ->where('in_cart.cart_id', $cart_id)
            ->orderBy('in_cart.sku')
            ->get([
                'in_cart.sku', 'in_cart.item_count',
                'product.product_name', 'product.price',
                'product.img_url', 'product.stock', 'product.status'
            ]);
    }

    # empties the cart but keeps it; returns the number of items removed
    public function clear_cart(int $cart_id):int{
        return DB::table(static::ITEM_TABLE)
            ->where('cart_id', $cart_id)
            ->delete();
    }
    
    public function delete_cart(int $cart_id):bool{
        return DB::table(static::CART_TABLE)
            ->where('cart_id', $cart_id)
            ->delete() > 0;
    }

    protected function is_foreign_key_violation(QueryException $e):bool{
        return ($e->errorInfo[0] ?? null) === '23503';
    }

    protected function is_check_violation(QueryException $e):bool{
        return ($e->errorInfo[0] ?? null) === '23514';
    }
}