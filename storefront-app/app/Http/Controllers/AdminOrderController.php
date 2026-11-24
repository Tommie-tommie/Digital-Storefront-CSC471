<?php

namespace App\Http\Controllers;

use App\Repositories\OrderRepository;
use Illuminate\View\View;

# admin sees every order (incl. ones whose customer got deleted, user_id = NULL)
class AdminOrderController extends Controller
{
    public function __construct(protected OrderRepository $orders){}

    public function index():View{
        return view('admin.orders.index', ['orders' => $this->orders->get_all_orders()]);
    }

    # unscoped get_order_by_id is fine here, route is behind the admin middleware
    public function show(int $order_id):View{
        $order = $this->orders->get_order_by_id($order_id);
        if (!$order) {
            abort(404);
        }
        return view('admin.orders.show', [
            'order' => $order,
            'items' => $this->orders->get_order_items($order_id)
        ]);
    }
}
