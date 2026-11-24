@extends('app')
@section('title', 'Checkout')

{{-- shipping info -> CheckoutController@store makes the order + empties the cart --}}
@section('content')
    <h1>Checkout</h1>
    <div class="pdp">
        <form method="POST" action="{{ route('checkout.store') }}" class="panel">
            @csrf
            <h2 style="margin-top:0">Shipping address</h2>

            {{-- saved addresses come from shipping_info --}}
            @foreach ($addresses as $address)
                <label class="address-option">
                    <input type="radio" name="address_id" value="{{ $address->address_id }}"
                        @checked(old('address_id', $loop->first ? $address->address_id : null) == $address->address_id)>
                    <span>
                        <strong>{{ $address->address_name ?: 'Saved address' }}</strong><br>
                        {{ $address->street_num }} {{ $address->street_name }}{{ $address->apt_number ? ', Apt ' . $address->apt_number : '' }}<br>
                        {{ $address->city }}, {{ $address->state }} {{ $address->postal_code }}
                    </span>
                </label>
            @endforeach
            @if ($addresses->isNotEmpty())
                <label class="address-option">
                    <input type="radio" name="address_id" value="" @checked(old('address_id') === '')>
                    <span><strong>Use a new address</strong> (fill in below)</span>
                </label>
            @endif

            <label for="address_name">Label (optional, e.g. Home)</label>
            <input type="text" id="address_name" name="address_name" value="{{ old('address_name') }}">
            <div class="row3">
                <div>
                    <label for="street_name">Street</label>
                    <input type="text" id="street_name" name="street_name" value="{{ old('street_name') }}">
                </div>
                <div>
                    <label for="street_num">Number</label>
                    <input type="text" id="street_num" name="street_num" value="{{ old('street_num') }}">
                </div>
                <div>
                    <label for="apt_number">Apt</label>
                    <input type="text" id="apt_number" name="apt_number" value="{{ old('apt_number') }}">
                </div>
            </div>
            <div class="row3">
                <div>
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" value="{{ old('city') }}">
                </div>
                <div>
                    <label for="state">State</label>
                    <input type="text" id="state" name="state" value="{{ old('state') }}">
                </div>
                <div>
                    <label for="postal_code">ZIP</label>
                    <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code') }}">
                </div>
            </div>
            <label class="address-option" style="border:none;padding:8px 0;background:none">
                <input type="checkbox" name="save_address" value="1" @checked(old('save_address', true))>
                <span>Save this address for next time</span>
            </label>

            <div class="actions">
                <button class="btn big" type="submit">Place order</button>
                <a href="{{ route('cart.index') }}">Back to cart</a>
            </div>
        </form>

        <div class="panel">
            <h2 style="margin-top:0">Order summary</h2>
            <table style="border:none">
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $item->product_name }} &times; {{ $item->item_count }}</td>
                        <td class="right">${{ number_format($item->price * $item->item_count / 100, 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td><strong>Total</strong></td>
                    <td class="right"><strong>${{ number_format($total / 100, 2) }}</strong></td>
                </tr>
            </table>
        </div>
    </div>
@endsection
