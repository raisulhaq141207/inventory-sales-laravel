<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Inventory') - Inventory & Sales</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
@auth
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="{{ route('dashboard') }}">Inventory &amp; Sales</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('products.index') }}">Produk</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('products.low-stock') }}">Stok Rendah</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('purchases.index') }}">Pembelian</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('sales.index') }}">Penjualan</a></li>
                @can('admin')
                    <li class="nav-item"><a class="nav-link" href="{{ route('categories.index') }}">Kategori</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('suppliers.index') }}">Pemasok</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('users.index') }}">Pengguna</a></li>
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
