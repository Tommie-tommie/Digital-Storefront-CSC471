@extends('app')
@section('title', $product->product_name)

{{-- PDP: add to cart form posts to CartController@add --}}
@section('content')
    <p><a href="{{ route('products.index') }}">&larr; Back to shop</a></p>
    <div class="pdp">
        <img src="{{ $product->img_url }}" alt="{{ $product->product_name }}">
        <div>
            <h1>{{ $product->product_name }}</h1>
            <p class="muted">SKU {{ $product->sku }}</p>
            <p class="price total">${{ number_format($product->price / 100, 2) }}</p>
            <p>{{ $product->description }}</p>
            <p class="muted">{{ $product->stock }} in stock</p>

            @if (session('user_type') === 'CUSTOMER')
                <form method="POST" action="{{ route('cart.add') }}" class="actions">
                    @csrf
                    <input type="hidden" name="sku" value="{{ $product->sku }}">
                    <input class="qty" type="number" name="quantity" value="1" min="1" max="99">
                    <button class="btn big" type="submit" @disabled($product->stock < 1)>
                        {{ $product->stock < 1 ? 'Out of stock' : 'Add to cart' }}
                    </button>
                </form>
            @else
                <p class="actions"><a class="btn big" href="{{ route('login') }}">Log in to add to cart</a></p>
            @endif
        </div>
    </div>
@endsection
