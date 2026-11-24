@extends('app')
@section('title', 'Sign up')

{{-- inserts into app_user + customer (UserRepository::insert_user) --}}
@section('content')
    <div class="narrow">
        <h1>Create an account</h1>
        <form method="POST" action="{{ route('register') }}" class="panel">
            @csrf
            <div class="row">
                <div>
                    <label for="first_name">First name</label>
                    <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                </div>
                <div>
                    <label for="last_name">Last name</label>
                    <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                </div>
            </div>
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            <label for="password">Password (8+ characters)</label>
            <input type="password" id="password" name="password" required>
            <label for="password_confirmation">Confirm password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required>
            <div class="actions">
                <button class="btn" type="submit">Sign up</button>
                <a href="{{ route('login') }}">I already have an account</a>
            </div>
        </form>
    </div>
@endsection
