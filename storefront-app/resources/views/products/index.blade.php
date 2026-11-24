@extends('app')
@section('title', 'Shop')

{{-- PLP: every ACTIVE product --}}
@section('content')
    <h1>Shop</h1>
    @if ($products->isEmpty())
        <p class="muted">No products yet.</p>
    @else
        <div class="grid">
            @foreach ($products as $product)
                <a class="card" href="{{ route('products.show', $product->sku) }}">
                    <img src="{{ $product->img_url }}" alt="{{ $product->product_name }}">
                    <div class="body">
                        <span class="name">{{ $product->product_name }}</span>
                        <span class="muted">SKU {{ $product->sku }}</span>
                        <span class="price">${{ number_format($product->price / 100, 2) }}</span>
                        @if ($product->stock < 1)
                            <span><span class="badge out">Out of stock</span></span>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif
@endsection
