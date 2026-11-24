<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Exceptions\InvalidCartDataException;
use App\Exceptions\ProductNotFoundException;
use App\Repositories\CartRepository;
use App\Repositories\ProductRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

# cart_id always comes from the logged in user, never from the form
class CartController extends Controller
{
    public function __construct(protected CartRepository $carts,
        protected ProductRepository $products){}

    public function index(Request $request):View{
        $cart_id = $this->carts->get_cart_id_by_user_id($request->session()->get('user_id'));
        $items = $cart_id? $this->carts->get_cart_items($cart_id): collect();
        $total = $items->sum(fn($item) => $item->price * $item->item_count);
        return view('cart.index', ['items' => $items, 'total' => $total]);
    }

    # from the PDP || upsert adds to the count if it's already in the cart
    public function add(Request $request):RedirectResponse{
        $data = $request->validate([
            'sku' => 'required|integer',
            'quantity' => 'required|integer|min:1|max:99'
        ]);
        $product = $this->products->get_product_by_sku($data['sku']);
        if (!$product || $product->status !== ProductStatus::ACTIVE->value) {
            return back()->withErrors(['quantity' => 'That product is not available.']);
        }
        $cart_id = $this->carts->get_or_create_cart_id($request->session()->get('user_id'));
        try {
            $this->carts->add_item($cart_id, $data['sku'], $data['quantity']);
        } catch (ProductNotFoundException|InvalidCartDataException $e) {
            return back()->withErrors(['quantity' => $e->getMessage()]);
        }
        return redirect()->route('cart.index')->with('status', $product->product_name . ' added to cart.');
    }

    # set exact quantity || 0 removes it (handled in the repo)
    public function update(Request $request, int $sku):RedirectResponse{
        $data = $request->validate(['quantity' => 'required|integer|min:0|max:99']);
        $cart_id = $this->carts->get_cart_id_by_user_id($request->session()->get('user_id'));
        if ($cart_id) {
            $this->carts->set_item_count($cart_id, $sku, $data['quantity']);
        }
        return redirect()->route('cart.index')->with('status', 'Cart updated.');
    }

    public function remove(Request $request, int $sku):RedirectResponse{
        $cart_id = $this->carts->get_cart_id_by_user_id($request->session()->get('user_id'));
        if ($cart_id) {
            $this->carts->remove_item($cart_id, $sku);
        }
        return redirect()->route('cart.index')->with('status', 'Item removed.');
    }
}
