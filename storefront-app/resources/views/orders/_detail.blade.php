{{-- shared by orders/show + admin/orders/show || item_price = price paid, not current price --}}
<div class="pdp">
    <div class="panel">
        <p><span class="badge {{ $order->status }}">{{ $order->status }}</span></p>
        <p class="muted">Placed {{ \Illuminate\Support\Carbon::parse($order->created_at)->format('M j, Y g:i A') }}</p>
        <h2>Shipping to</h2>
        <p>
            {{ $order->street_num }} {{ $order->street_name }}{{ $order->apt_number ? ', Apt ' . $order->apt_number : '' }}<br>
            {{ $order->city }}, {{ $order->state }} {{ $order->postal_code }}
        </p>
        @if ($order->shipment_id)
            <p class="muted">Tracking: {{ $order->shipment_id }}</p>
        @endif
    </div>
    <div>
        <table>
            <thead><tr><th></th><th>Product</th><th>Qty</th><th>Price paid</th><th class="right">Subtotal</th></tr></thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td><img class="thumb" src="{{ $item->img_url }}" alt=""></td>
                        <td>{{ $item->product_name }}</td>
                        <td>{{ $item->item_count }}</td>
                        <td>${{ number_format($item->item_price / 100, 2) }}</td>
                        <td class="right">${{ number_format($item->item_price * $item->item_count / 100, 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="4"><strong>Total</strong></td>
                    <td class="right"><strong>${{ number_format($order->total_price / 100, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
