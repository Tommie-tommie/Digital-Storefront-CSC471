@extends('app')
@section('title', 'Manage products')

{{-- admin product list: every status, not just ACTIVE --}}
@section('content')
    <div class="actions" style="margin:0 0 18px;justify-content:space-between">
        <h1 style="margin:0">Products</h1>
        <a class="btn" href="{{ route('admin.products.create') }}">+ Add product</a>
    </div>
    <table>
        <thead>
            <tr><th>SKU</th><th></th><th>Name</th><th>Price</th><th>Stock</th><th>Status</th><th class="right">Actions</th></tr>
        </thead>
        <tbody>
            @forelse ($products as $product)
                <tr>
                    <td>{{ $product->sku }}</td>
                    <td><img class="thumb" src="{{ $product->img_url }}" alt=""></td>
                    <td>{{ $product->product_name }}</td>
                    <td>${{ number_format($product->price / 100, 2) }}</td>
                    <td>{{ $product->stock }}</td>
                    <td><span class="badge {{ $product->status }}">{{ $product->status }}</span></td>
                    <td class="right">
                        <a class="btn secondary" href="{{ route('admin.products.edit', $product->sku) }}">Edit</a>
                        {{-- soft delete: sets status RETIRED so customers can't see it --}}
                        @if ($product->status !== 'RETIRED')
                            <form method="POST" action="{{ route('admin.products.retire', $product->sku) }}" class="inline"
                                onsubmit="return confirm('Retire {{ addslashes($product->product_name) }}? It will be hidden from the shop.')">
                                @csrf
                                @method('PATCH')
                                <button class="btn danger" type="submit">Retire</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="muted">No products yet.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
