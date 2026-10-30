<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Inventory') - Inventory & Sales</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo.svg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
@auth
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ auth()->user()->isCustomer() ? route('shop.index') : route('dashboard') }}">
            <img src="{{ asset('images/logo.svg') }}" alt="Logo" width="32" height="32">
            <span>Inventory &amp; Sales</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav me-auto">
                @can('staff-area')
                    <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('products.index') }}">Produk</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('products.low-stock') }}">Stok Rendah</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('purchases.index') }}">Pembelian</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('sales.index') }}">Penjualan</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('orders.index') }}">Pesanan Online</a></li>
                    @can('admin')
                        <li class="nav-item"><a class="nav-link" href="{{ route('categories.index') }}">Kategori</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('suppliers.index') }}">Pemasok</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('users.index') }}">Pengguna</a></li>
                    @endcan
                @endcan
                @can('customer')
                    <li class="nav-item"><a class="nav-link" href="{{ route('shop.index') }}">Katalog</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('my-orders.index') }}">Pesanan Saya</a></li>
                @endcan
            </ul>
            <span class="navbar-text me-3">{{ auth()->user()->name }} ({{ auth()->user()->role }})</span>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="btn btn-outline-light btn-sm">Logout</button>
            </form>
        </div>
    </div>
</nav>
@endauth

<main class="container pb-5">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if ($errors->has('cancel'))
        <div class="alert alert-danger">{{ $errors->first('cancel') }}</div>
    @endif

    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
