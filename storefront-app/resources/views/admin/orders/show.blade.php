@extends('app')
@section('title', 'Order #' . $order->order_id)

@section('content')
    <p><a href="{{ route('admin.orders.index') }}">&larr; All orders</a></p>
    <h1>Order #{{ $order->order_id }}</h1>
    @include('orders._detail')
@endsection
