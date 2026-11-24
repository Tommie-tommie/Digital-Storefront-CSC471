<?php

namespace App\Http\Controllers;

use App\Repositories\OrderRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;

# customer order listing page
class OrderController extends Controller
{
    public function __construct(protected OrderRepository $orders){}

    public function index(Request $request):View{
        return view('orders.index', [
            'orders' => $this->orders->get_orders_by_user_id($request->session()->get('user_id'))
        ]);
    }

    # get_user_order is scoped by user_id, changing the id in the url won't show other people's orders
    public function show(Request $request, int $order_id):View{
        $order = $this->orders->get_user_order($order_id, $request->session()->get('user_id'));
        if (!$order) {
            abort(404);
        }
        return view('orders.show', [
            'order' => $order,
            'items' => $this->orders->get_order_items($order_id)
        ]);
    }
}
