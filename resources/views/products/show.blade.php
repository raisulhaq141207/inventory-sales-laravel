@extends('layouts.app')
@section('title', 'Detail Produk')

@section('content')
<h1 class="h4 mb-3">Detail Produk</h1>
<div class="card"><div class="card-body">
    <dl class="row mb-0">
        <dt class="col-sm-3">SKU</dt><dd class="col-sm-9">{{ $product->sku }}</dd>
        <dt class="col-sm-3">Nama</dt><dd class="col-sm-9">{{ $product->name }}</dd>
        <dt class="col-sm-3">Kategori</dt><dd class="col-sm-9">{{ $product->category->name }}</dd>
        <dt class="col-sm-3">Harga Beli</dt><dd class="col-sm-9">Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</dd>
        <dt class="col-sm-3">Harga Jual</dt><dd class="col-sm-9">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</dd>
        <dt class="col-sm-3">Stok</dt>
        <dd class="col-sm-9">{{ $product->stock }}
            @if ($product->isLowStock()) <span class="badge bg-danger">Rendah</span> @endif
        </dd>
        <dt class="col-sm-3">Stok Minimum</dt><dd class="col-sm-9">{{ $product->minimum_stock }}</dd>
    </dl>
</div></div>
<a href="{{ route('products.index') }}" class="btn btn-link mt-2">&larr; Kembali</a>
@endsection
