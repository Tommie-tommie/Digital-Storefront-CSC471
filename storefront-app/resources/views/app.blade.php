<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Storefront') · Digital Storefront</title>
    {{-- plain css so there's no npm build step for the demo --}}
    <style>
        *{box-sizing:border-box}
        body{margin:0;font-family:system-ui,-apple-system,"Segoe UI",sans-serif;background:#f5f5f5;color:#1a1a1a;line-height:1.5}
        a{color:inherit}
        header{background:#fff;color:#1a1a1a;border-bottom:1px solid #ddd}
        header .bar{max-width:1100px;margin:0 auto;padding:14px 20px;display:flex;align-items:center;gap:20px}
        header .brand{font-weight:700;font-size:18px;text-decoration:none;margin-right:auto}
        header .brand span{color:#888}
        header nav{display:flex;align-items:center;gap:16px;font-size:14px}
        header nav a{text-decoration:none;opacity:.75}
        header nav a:hover{opacity:1}
        header .admin-tag{background:#333;color:#fff;border-radius:4px;padding:2px 8px;font-size:12px;font-weight:700}
        main{max-width:1100px;margin:0 auto;padding:28px 20px 60px}
        h1{font-size:26px;margin:0 0 18px}
        h2{font-size:19px;margin:28px 0 12px}
        .muted{color:#777}
        .flash{background:#fff;border:1px solid #ccc;border-left:4px solid #333;padding:10px 14px;border-radius:6px;margin-bottom:18px}
        .errors{background:#fff;border:1px solid #ccc;border-left:4px solid #999;padding:10px 14px;border-radius:6px;margin-bottom:18px}
        .errors ul{margin:0;padding-left:18px}
        .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:18px}
        .card{background:#fff;border:1px solid #ddd;border-radius:10px;overflow:hidden;display:flex;flex-direction:column;text-decoration:none}
        .card:hover{border-color:#bbb}
        .card img{width:100%;aspect-ratio:4/3;object-fit:cover;background:#eee;display:block}
        .card .body{padding:12px 14px;display:flex;flex-direction:column;gap:4px;flex:1}
        .card .name{font-weight:600}
        .price{font-weight:700}
        .badge{display:inline-block;font-size:12px;padding:2px 8px;border-radius:999px;background:#e5e5e5;color:#333;border:1px solid transparent}
        .badge.ACTIVE,.badge.DELIVERED{background:#333;color:#fff}
        .badge.RETIRED,.badge.CANCELED{background:#fff;color:#888;border-color:#ccc}
        .badge.PLANNING,.badge.PENDING,.badge.PROCESSING{background:#e5e5e5;color:#333}
        .badge.SHIPPED,.badge.DELIVERING{background:#999;color:#fff}
        .badge.out{background:#fff;color:#666;border-color:#bbb}
        .pdp{display:grid;grid-template-columns:1fr 1fr;gap:32px;align-items:start}
        .pdp img{width:100%;border-radius:10px;border:1px solid #ddd;background:#eee}
        @media (max-width:760px){.pdp{grid-template-columns:1fr}}
        .panel{background:#fff;border:1px solid #ddd;border-radius:10px;padding:20px}
        table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #ddd;border-radius:10px;overflow:hidden}
        th,td{text-align:left;padding:10px 12px;border-bottom:1px solid #eee;vertical-align:middle;font-size:14px}
        th{background:#fafafa;font-weight:600}
        tr:last-child td{border-bottom:none}
        td img.thumb{width:56px;height:42px;object-fit:cover;border-radius:4px;background:#eee}
        .right{text-align:right}
        form.inline{display:inline-flex;gap:6px;align-items:center;margin:0}
        label{display:block;font-size:14px;font-weight:600;margin:12px 0 4px}
        input[type=text],input[type=email],input[type=password],input[type=number],input[type=url],textarea,select{width:100%;padding:8px 10px;border:1px solid #ccc;border-radius:6px;font:inherit;background:#fff}
        input.qty{width:70px}
        textarea{min-height:110px}
        .row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
        .row3{display:grid;grid-template-columns:2fr 1fr 1fr;gap:12px}
        .btn{display:inline-block;background:#1a1a1a;color:#fff;border:1px solid #1a1a1a;border-radius:6px;padding:8px 16px;font:inherit;font-size:14px;cursor:pointer;text-decoration:none}
        .btn:hover{background:#000}
        .btn.secondary{background:#fff;color:#1a1a1a;border-color:#ccc}
        .btn.danger{background:#fff;color:#555;border-color:#bbb}
        .btn.big{padding:11px 22px;font-size:15px}
        .btn:disabled{opacity:.45;cursor:not-allowed}
        .actions{display:flex;gap:10px;align-items:center;margin-top:18px}
        .narrow{max-width:420px;margin:0 auto}
        .total{font-size:18px}
        .address-option{display:flex;gap:10px;align-items:flex-start;font-weight:400;border:1px solid #ddd;border-radius:8px;padding:10px 12px;margin:8px 0;background:#fff;cursor:pointer}
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
