<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Exceptions\InvalidOrderDataException;
use App\Repositories\CartRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\ShippingInfoRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

# cart -> order || the whole thing is one transaction
class CheckoutController extends Controller
{
    public function __construct(protected CartRepository $carts,
        protected ProductRepository $products,
        protected OrderRepository $orders,
        protected ShippingInfoRepository $addresses){}

    public function show(Request $request):View|RedirectResponse{
        $user_id = $request->session()->get('user_id');
        $cart_id = $this->carts->get_cart_id_by_user_id($user_id);
        $items = $cart_id? $this->carts->get_cart_items($cart_id): collect();
        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Your cart is empty.']);
        }
        return view('checkout.show', [
            'items' => $items,
            'total' => $items->sum(fn($item) => $item->price * $item->item_count),
            'addresses' => $this->addresses->get_addresses_by_user_id($user_id)
        ]);
    }

    public function store(Request $request):RedirectResponse{
        $user_id = $request->session()->get('user_id');
        # either pick a saved address or fill in a new one
        $data = $request->validate([
            'address_id' => 'nullable|integer',
            'address_name' => 'nullable|max:100',
            'street_num' => 'required_without:address_id|nullable|max:20',
            'street_name' => 'required_without:address_id|nullable|max:100',
            'apt_number' => 'nullable|max:20',
            'city' => 'required_without:address_id|nullable|max:100',
            'state' => 'required_without:address_id|nullable|max:100',
            'postal_code' => 'required_without:address_id|nullable|max:50',
            'save_address' => 'nullable|boolean'
        ], ['required_without' => 'Please fill in the :attribute (or pick a saved address).']);

        if (!empty($data['address_id'])) {
            # scoped by user_id so you can't use someone else's address
            $address = $this->addresses->get_address_by_id($data['address_id'], $user_id);
            if (!$address) {
                return back()->withErrors(['address_id' => 'Address not found.']);
            }
            $address = (array) $address;
        } else {
            $address = $data;
        }

        $cart_id = $this->carts->get_cart_id_by_user_id($user_id);
        if (!$cart_id) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Your cart is empty.']);
        }

        # throwing anywhere inside rolls back the stock changes, order, and cart delete
        try {
            $order_id = DB::transaction(function () use ($user_id, $cart_id, $address, $data) {
                $cart_items = $this->carts->get_cart_items($cart_id);
                if ($cart_items->isEmpty()) {
                    throw new InvalidOrderDataException('Your cart is empty.');
                }
                $order_items = [];
                foreach ($cart_items as $item) {
                    if ($item->status !== ProductStatus::ACTIVE->value) {
                        throw new InvalidOrderDataException($item->product_name . ' is no longer available.');
                    }
                    # atomic "stock >= n" check, false = not enough
                    if (!$this->products->decrease_stock($item->sku, $item->item_count)) {
                        throw new InvalidOrderDataException('Not enough stock for ' . $item->product_name . '.');
                    }
                    # snapshot the price at purchase time
                    $order_items[] = [
                        'sku' => $item->sku,
                        'item_count' => $item->item_count,
                        'item_price' => $item->price
                    ];
                }
                if (empty($data['address_id']) && !empty($data['save_address'])) {
                    $this->addresses->insert_address($user_id, $address['address_name'] ?? null,
                        $address['street_name'], $address['street_num'], $address['apt_number'] ?? null,
                        $address['city'], $address['state'], $address['postal_code']);
                }
                $order_id = $this->orders->insert_order($user_id, $order_items,
                    $address['street_name'], $address['street_num'], $address['apt_number'] ?? null,
                    $address['city'], $address['state'], $address['postal_code']);
                # empty the cart (in_cart rows cascade)
                $this->carts->delete_cart($cart_id);
                return $order_id;
            });
        } catch (InvalidOrderDataException $e) {
            return back()->withErrors(['cart' => $e->getMessage()])->withInput();
        }

        return redirect()->route('orders.show', $order_id)->with('status', 'Order placed!');
    }
}
