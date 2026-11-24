@extends('app')
@section('title', 'Admin login')

{{-- checks app_admin instead of customer --}}
@section('content')
    <div class="narrow">
        <h1>Admin login</h1>
        <form method="POST" action="{{ route('admin.login') }}" class="panel">
            @csrf
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            <div class="actions">
                <button class="btn" type="submit">Log in</button>
            </div>
        </form>
    </div>
@endsection
