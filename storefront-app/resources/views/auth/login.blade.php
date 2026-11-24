@extends('app')
@section('title', 'Log in')

@section('content')
    <div class="narrow">
        <h1>Log in</h1>
        <form method="POST" action="{{ route('login') }}" class="panel">
            @csrf
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            <div class="actions">
                <button class="btn" type="submit">Log in</button>
                <a href="{{ route('register') }}">Create an account</a>
            </div>
        </form>
        <p class="muted">Admin? <a href="{{ route('admin.login') }}">Admin login</a></p>
    </div>
@endsection
