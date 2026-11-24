<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Repositories\ProductRepository;
use Illuminate\View\View;

# public catalog: PLP + PDP
class ProductController extends Controller
{
    public function __construct(protected ProductRepository $products){}

    # PLP || only ACTIVE products, out of stock ones still show but can't be added
    public function index():View{
        return view('products.index', [
            'products' => $this->products->get_products_by_status(ProductStatus::ACTIVE)
        ]);
    }

    # PDP || 404 for retired/planning so people can't guess skus
    public function show(int $sku):View{
        $product = $this->products->get_product_by_sku($sku);
        if (!$product || $product->status !== ProductStatus::ACTIVE->value) {
            abort(404);
        }
        return view('products.show', ['product' => $product]);
    }
}
