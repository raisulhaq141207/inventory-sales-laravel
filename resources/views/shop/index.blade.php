@extends('layouts.app')
@section('title', 'Katalog')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Katalog Produk</h1>
    <a href="{{ route('my-orders.create') }}" class="btn btn-primary">+ Buat Pesanan</a>
</div>

<form method="GET" action="{{ route('shop.index') }}" class="row g-2 mb-3">
    <div class="col-md-5">
        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari produk">
    </div>
    <div class="col-md-4">
        <select name="category_id" class="form-select">
            <option value="">Semua kategori</option>
            @foreach ($categories as $c)
                <option value="{{ $c->id }}" @selected(request('category_id') == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-secondary">Cari</button>
        <a href="{{ route('shop.index') }}" class="btn btn-outline-secondary">Reset</a>
    </div>
</form>

<div class="row g-3">
    @forelse ($products as $p)
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small">{{ $p->category->name }}</div>
                    <h2 class="h6 mt-1">{{ $p->name }}</h2>
                    <div class="fw-semibold">Rp {{ number_format($p->selling_price, 0, ',', '.') }}</div>
                    <div class="small mt-1">
                        Stok: {{ $p->stock }}
                        @if ($p->isLowStock()) <span class="badge bg-warning text-dark">Terbatas</span> @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center text-muted py-5">Produk tidak ditemukan.</div>
    @endforelse
</div>
<div class="mt-3">{{ $products->links() }}</div>
@endsection
