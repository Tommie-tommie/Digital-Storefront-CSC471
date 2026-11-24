@extends('app')
@section('title', 'Cart')

{{-- prices here are live from product (join in get_cart_items), they get snapshotted at checkout --}}
@section('content')
    <h1>Your cart</h1>
    @if ($items->isEmpty())
        <p class="muted">Your cart is empty. <a href="{{ route('products.index') }}">Go shopping</a></p>
    @else
        <table>
            <thead>
                <tr><th></th><th>Product</th><th>Price</th><th>Quantity</th><th class="right">Subtotal</th><th></th></tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td><img class="thumb" src="{{ $item->img_url }}" alt=""></td>
                        <td>
                            <a href="{{ route('products.show', $item->sku) }}">{{ $item->product_name }}</a>
                            @if ($item->status !== 'ACTIVE')
                                <br><span class="badge out">No longer available</span>
                            @elseif ($item->item_count > $item->stock)
                                <br><span class="badge out">Only {{ $item->stock }} in stock</span>
                            @endif
                        </td>
                        <td>${{ number_format($item->price / 100, 2) }}</td>
                        <td>
                            {{-- 0 removes the item --}}
                            <form method="POST" action="{{ route('cart.update', $item->sku) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <input class="qty" type="number" name="quantity" value="{{ $item->item_count }}" min="0" max="99">
                                <button class="btn secondary" type="submit">Update</button>
                            </form>
                        </td>
                        <td class="right">${{ number_format($item->price * $item->item_count / 100, 2) }}</td>
                        <td class="right">
                            <form method="POST" action="{{ route('cart.remove', $item->sku) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn danger" type="submit">Remove</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="actions" style="justify-content:flex-end">
            <span class="total">Total: <strong>${{ number_format($total / 100, 2) }}</strong></span>
            <a class="btn big secondary" href="{{ route('products.index') }}">Continue shopping</a>
            <a class="btn big" href="{{ route('checkout.show') }}">Checkout</a>
        </div>
    @endif
@endsection
