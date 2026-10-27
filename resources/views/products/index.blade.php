@extends('layouts.app')
@section('title', $lowOnly ? 'Stok Rendah' : 'Produk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">{{ $lowOnly ? 'Produk Stok Rendah' : 'Daftar Produk' }}</h1>
    @can('admin')
        <a href="{{ route('products.create') }}" class="btn btn-primary">+ Tambah Produk</a>
    @endcan
</div>

<form method="GET" action="{{ $lowOnly ? route('products.low-stock') : route('products.index') }}" class="row g-2 mb-3">
    <div class="col-md-5">
        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari SKU / nama produk">
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
        <a href="{{ $lowOnly ? route('products.low-stock') : route('products.index') }}" class="btn btn-outline-secondary">Reset</a>
    </div>
</form>
@error('q') <div class="text-danger small mb-2">{{ $message }}</div> @enderror

<div class="card">
    <div class="table-responsive">
        <table class="table table-striped mb-0 align-middle">
            <thead>
                <tr>
                    <th>SKU</th><th>Nama</th><th>Kategori</th>
                    <th class="text-end">Harga Jual</th><th class="text-end">Stok</th><th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($products as $p)
                <tr>
                    <td>{{ $p->sku }}</td>
                    <td>{{ $p->name }}</td>
                    <td>{{ $p->category->name }}</td>
                    <td class="text-end">Rp {{ number_format($p->selling_price, 0, ',', '.') }}</td>
                    <td class="text-end">
                        {{ $p->stock }}
                        @if ($p->isLowStock()) <span class="badge bg-danger">Rendah</span> @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('products.show', $p) }}" class="btn btn-sm btn-outline-secondary">Detail</a>
                        @can('admin')
                            <a href="{{ route('products.edit', $p) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form action="{{ route('products.destroy', $p) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Hapus produk ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada produk.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $products->links() }}</div>
@endsection
