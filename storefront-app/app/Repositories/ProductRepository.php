<?php
namespace App\Repositories;

use App\Enums\ProductStatus;
use App\Exceptions\InvalidProductDataException;
use App\Exceptions\ProductInUseException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductRepository {
    protected const string TABLE = 'product';

    # price is in cents || returns the new sku
    public function insert_product(string $product_name, string $description, int $price,
        int $stock = 0, string $img_url = '', ProductStatus $status = ProductStatus::PLANNING):int{
        try {
            return DB::table(static::TABLE)->insertGetId([
                'product_name' => $product_name,
                'description' => $description,
                'price' => $price,
                'stock' => $stock,
                'img_url' => $img_url,
                'status' => $status->value
            ], 'sku');
        } catch (QueryException $e) {
            if ($this->is_check_violation($e)) {
                throw new InvalidProductDataException('Price must be positive and stock cannot be negative.', 0, $e);
            }
            throw $e;
        }
    }

    public function get_product_by_sku(int $sku):?object{
        return DB::table(static::TABLE)
            ->where('sku', $sku)->first();
    }

    # admin list, everything regardless of status
    public function get_all_products():Collection{
        return DB::table(static::TABLE)
            ->orderBy('sku')
            ->get();
    }

    public function get_products_by_status(ProductStatus $status):Collection{
        return DB::table(static::TABLE)
            ->where('status', $status->value)
            ->orderBy('sku')
            ->get();
    }

    public function get_active_products_in_stock():Collection{
        return DB::table(static::TABLE)
            ->where('status', ProductStatus::ACTIVE->value)
            ->where('stock', '>', 0)
            ->orderBy('sku')
            ->get();
    }

    public function update_product(int $sku, string $product_name, string $description,
        int $price, string $img_url):bool{
        try {
            return DB::table(static::TABLE)
                ->where('sku', $sku)
                ->update([
                    'product_name' => $product_name,
                    'description' => $description,
                    'price' => $price,
                    'img_url' => $img_url
                ]) > 0;
        } catch (QueryException $e) {
            if ($this->is_check_violation($e)) {
                throw new InvalidProductDataException('Price must be positive.', 0, $e);
            }
            throw $e;
        }
    }

    public function update_product_status(int $sku, ProductStatus $status):bool{
        return DB::table(static::TABLE)
            ->where('sku', $sku)
            ->update(['status' => $status->value]) > 0;
    }

    public function increase_stock(int $sku, int $amount):bool{
        return DB::table(static::TABLE)
            ->where('sku', $sku)
            ->increment('stock', $amount) > 0;
    }

    # atomic: only decrements if there's enough || false = no product OR not enough stock
    public function decrease_stock(int $sku, int $amount):bool{
        return DB::table(static::TABLE)
            ->where('sku', $sku)
            ->where('stock', '>=', $amount)
            ->decrement('stock', $amount) > 0;
    }

    # fails w/ ProductInUseException if it's in a cart or an order (ON DELETE RESTRICT)
    public function delete_product(int $sku):bool{
        try {
            return DB::table(static::TABLE)
                ->where('sku', $sku)
                ->delete() > 0;
        } catch (QueryException $e) {
            if (($e->errorInfo[0] ?? null) === '23503') {
                throw new ProductInUseException('Product is in a cart or an order.', 0, $e);
            }
            throw $e;
        }
    }

    protected function is_check_violation(QueryException $e):bool{
        return ($e->errorInfo[0] ?? null) === '23514';
    }
}
