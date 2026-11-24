@extends('app')
@section('title', $product ? 'Edit product' : 'Add product')

{{-- one form for both add + edit || $product is null when adding --}}
@section('content')
    <p><a href="{{ route('admin.products.index') }}">&larr; All products</a></p>
    <h1>{{ $product ? 'Edit product #' . $product->sku : 'Add product' }}</h1>
    <form method="POST" class="panel" style="max-width:720px"
        action="{{ $product ? route('admin.products.update', $product->sku) : route('admin.products.store') }}">
        @csrf
        @if ($product)
            @method('PUT')
        @endif

        <label for="product_name">Name</label>
        <input type="text" id="product_name" name="product_name" maxlength="100" required
            value="{{ old('product_name', $product->product_name ?? '') }}">

        <label for="description">Description</label>
        <textarea id="description" name="description" required>{{ old('description', $product->description ?? '') }}</textarea>

        <div class="row">
            <div>
                {{-- dollars in the form, cents in the db --}}
                <label for="price">Price ($)</label>
                <input type="number" id="price" name="price" step="0.01" min="0.01" required
                    value="{{ old('price', $product ? number_format($product->price / 100, 2, '.', '') : '') }}">
            </div>
            <div>
                <label for="status">Status</label>
                <select id="status" name="status">
                    @foreach (\App\Enums\ProductStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected(old('status', $product->status ?? 'PLANNING') === $status->value)>
                            {{ $status->value }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row">
            <div>
                <label for="stock">Stock</label>
                <input type="number" id="stock" name="stock" min="0" required
                    value="{{ old('stock', $product->stock ?? 0) }}">
                @if ($product)
                    {{-- what stock was when the page loaded, controller applies the difference --}}
                    <input type="hidden" name="original_stock" value="{{ old('original_stock', $product->stock) }}">
                @endif
            </div>
            <div>
                <label for="img_url">Image URL</label>
                <input type="text" id="img_url" name="img_url" value="{{ old('img_url', $product->img_url ?? '') }}"
                    placeholder="/images/products/example.svg or https://...">
            </div>
        </div>

        <div class="actions">
            <button class="btn" type="submit">{{ $product ? 'Save changes' : 'Add product' }}</button>
            <a href="{{ route('admin.products.index') }}">Cancel</a>
        </div>
    </form>
@endsection
