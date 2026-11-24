<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Storefront') · Digital Storefront</title>
    {{-- plain css so there's no npm build step for the demo --}}
    <style>
        *{box-sizing:border-box}
        body{margin:0;font-family:system-ui,-apple-system,"Segoe UI",sans-serif;background:#f6f5f2;color:#1d1d1b;line-height:1.5}
        a{color:inherit}
        header{background:#1d1d1b;color:#fff}
        header .bar{max-width:1100px;margin:0 auto;padding:14px 20px;display:flex;align-items:center;gap:20px}
        header .brand{font-weight:700;font-size:18px;text-decoration:none;margin-right:auto}
        header .brand span{color:#f2a541}
        header nav{display:flex;align-items:center;gap:16px;font-size:14px}
        header nav a{text-decoration:none;opacity:.85}
        header nav a:hover{opacity:1}
        header .admin-tag{background:#f2a541;color:#1d1d1b;border-radius:4px;padding:2px 8px;font-size:12px;font-weight:700}
        main{max-width:1100px;margin:0 auto;padding:28px 20px 60px}
        h1{font-size:26px;margin:0 0 18px}
        h2{font-size:19px;margin:28px 0 12px}
        .muted{color:#6f6e69}
        .flash{background:#e7f4ea;border:1px solid #b7dfc1;padding:10px 14px;border-radius:6px;margin-bottom:18px}
        .errors{background:#fdecea;border:1px solid #f5c2bd;padding:10px 14px;border-radius:6px;margin-bottom:18px}
        .errors ul{margin:0;padding-left:18px}
        .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:18px}
        .card{background:#fff;border:1px solid #e4e2dc;border-radius:10px;overflow:hidden;display:flex;flex-direction:column;text-decoration:none}
        .card:hover{border-color:#c9c6bd}
        .card img{width:100%;aspect-ratio:4/3;object-fit:cover;background:#eceae4;display:block}
        .card .body{padding:12px 14px;display:flex;flex-direction:column;gap:4px;flex:1}
        .card .name{font-weight:600}
        .price{font-weight:700}
        .badge{display:inline-block;font-size:12px;padding:2px 8px;border-radius:999px;background:#eceae4}
        .badge.ACTIVE,.badge.DELIVERED{background:#e7f4ea;color:#1f6b35}
        .badge.RETIRED,.badge.CANCELED{background:#fdecea;color:#9b2c22}
        .badge.PLANNING,.badge.PENDING,.badge.PROCESSING{background:#fff4e0;color:#8a5a00}
        .badge.SHIPPED,.badge.DELIVERING{background:#e6effc;color:#1f4c8f}
        .badge.out{background:#fdecea;color:#9b2c22}
        .pdp{display:grid;grid-template-columns:1fr 1fr;gap:32px;align-items:start}
        .pdp img{width:100%;border-radius:10px;border:1px solid #e4e2dc;background:#eceae4}
        @media (max-width:760px){.pdp{grid-template-columns:1fr}}
        .panel{background:#fff;border:1px solid #e4e2dc;border-radius:10px;padding:20px}
        table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #e4e2dc;border-radius:10px;overflow:hidden}
        th,td{text-align:left;padding:10px 12px;border-bottom:1px solid #eceae4;vertical-align:middle;font-size:14px}
        th{background:#faf9f6;font-weight:600}
        tr:last-child td{border-bottom:none}
        td img.thumb{width:56px;height:42px;object-fit:cover;border-radius:4px;background:#eceae4}
        .right{text-align:right}
        form.inline{display:inline-flex;gap:6px;align-items:center;margin:0}
        label{display:block;font-size:14px;font-weight:600;margin:12px 0 4px}
        input[type=text],input[type=email],input[type=password],input[type=number],input[type=url],textarea,select{width:100%;padding:8px 10px;border:1px solid #cfccc3;border-radius:6px;font:inherit;background:#fff}
        input.qty{width:70px}
        textarea{min-height:110px}
        .row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
        .row3{display:grid;grid-template-columns:2fr 1fr 1fr;gap:12px}
        .btn{display:inline-block;background:#1d1d1b;color:#fff;border:1px solid #1d1d1b;border-radius:6px;padding:8px 16px;font:inherit;font-size:14px;cursor:pointer;text-decoration:none}
        .btn:hover{background:#000}
        .btn.secondary{background:#fff;color:#1d1d1b;border-color:#cfccc3}
        .btn.danger{background:#fff;color:#9b2c22;border-color:#f0b9b3}
        .btn.big{padding:11px 22px;font-size:15px}
        .btn:disabled{opacity:.45;cursor:not-allowed}
        .actions{display:flex;gap:10px;align-items:center;margin-top:18px}
        .narrow{max-width:420px;margin:0 auto}
        .total{font-size:18px}
        .address-option{display:flex;gap:10px;align-items:flex-start;font-weight:400;border:1px solid #e4e2dc;border-radius:8px;padding:10px 12px;margin:8px 0;background:#fff;cursor:pointer}
        .address-option input{margin-top:4px}
    </style>
</head>
<body>
<header>
    <div class="bar">
        {{-- nav changes based on who's logged in (session user_type) --}}
        @if (session('user_type') === 'ADMIN')
            <a class="brand" href="{{ route('admin.products.index') }}">Digital<span>Storefront</span></a>
            <nav>
                <span class="admin-tag">ADMIN</span>
                <a href="{{ route('admin.products.index') }}">Products</a>
                <a href="{{ route('admin.orders.index') }}">Orders</a>
                <a href="{{ route('products.index') }}">View store</a>
                <form method="POST" action="{{ route('admin.logout') }}" class="inline">
                    @csrf
                    <button class="btn secondary" type="submit">Log out</button>
                </form>
            </nav>
        @else
            <a class="brand" href="{{ route('products.index') }}">Digital<span>Storefront</span></a>
            <nav>
                <a href="{{ route('products.index') }}">Shop</a>
                @if (session('user_type') === 'CUSTOMER')
                    <a href="{{ route('cart.index') }}">Cart</a>
                    <a href="{{ route('orders.index') }}">My orders</a>
                    <span class="muted">Hi, {{ session('user_name') }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button class="btn secondary" type="submit">Log out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}">Log in</a>
                    <a class="btn secondary" href="{{ route('register') }}">Sign up</a>
                @endif
            </nav>
        @endif
    </div>
</header>
<main>
    @if (session('status'))
        <div class="flash">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="errors">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>
</body>
</html>
