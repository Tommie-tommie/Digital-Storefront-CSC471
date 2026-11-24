@extends('app')
@section('title', 'All orders')

{{-- every order from every customer --}}
@section('content')
    <h1>All orders</h1>
    @if ($orders->isEmpty())
        <p class="muted">No orders yet.</p>
    @else
        <table>
            <thead>
                <tr><th>Order #</th><th>Customer</th><th>Placed</th><th>Ship to</th><th>Status</th><th class="right">Total</th><th></th></tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                    <tr>
                        <td>{{ $order->order_id }}</td>
                        {{-- user_id is NULL if the customer was deleted (ON DELETE SET NULL) --}}
                        <td>{{ $order->email ?? 'deleted customer' }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($order->created_at)->format('M j, Y g:i A') }}</td>
                        <td>{{ $order->city }}, {{ $order->state }}</td>
                        <td><span class="badge {{ $order->status }}">{{ $order->status }}</span></td>
                        <td class="right">${{ number_format($order->total_price / 100, 2) }}</td>
                        <td class="right"><a class="btn secondary" href="{{ route('admin.orders.show', $order->order_id) }}">View</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
