@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<h1 class="h4 mb-3">Dashboard</h1>

<div class="row g-3 mb-4">
    @php
        $cards = [
            ['Total Produk', $stats['total_products'], 'primary'],
            ['Produk Stok Rendah', $stats['low_stock'], 'danger'],
            ['Transaksi Penjualan', $stats['sales_count'], 'success'],
            ['Transaksi Pembelian', $stats['purchases_count'], 'secondary'],
        ];
    @endphp
    @foreach ($cards as [$label, $value, $color])
        <div class="col-6 col-lg-3">
            <div class="card border-{{ $color }}">
                <div class="card-body">
                    <div class="text-muted small">{{ $label }}</div>
                    <div class="fs-3 fw-semibold">{{ $value }}</div>
                </div>
            </div>
        </div>
    @endforeach
    <div class="col-md-6">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Penjualan Hari Ini</div>
            <div class="fs-4 fw-semibold">Rp {{ number_format($stats['sales_today'], 0, ',', '.') }}</div>
        </div></div>
    </div>
    <div class="col-md-6">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Penjualan Bulan Ini</div>
            <div class="fs-4 fw-semibold">Rp {{ number_format($stats['sales_month'], 0, ',', '.') }}</div>
        </div></div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <span>Stok Rendah</span>
                <a href="{{ route('products.low-stock') }}" class="small">Lihat semua</a>
            </div>
            <ul class="list-group list-group-flush">
                @forelse ($lowStockProducts as $p)
                    <li class="list-group-item d-flex justify-content-between">
                        <span>{{ $p->name }} <small class="text-muted">({{ $p->category->name }})</small></span>
                        <span class="text-danger">{{ $p->stock }} / min {{ $p->minimum_stock }}</span>
                    </li>
                @empty
                    <li class="list-group-item text-muted">Tidak ada produk stok rendah.</li>
                @endforelse
            </ul>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <span>Penjualan Terbaru</span>
                <a href="{{ route('sales.index') }}" class="small">Lihat semua</a>
            </div>
            <ul class="list-group list-group-flush">
                @forelse ($recentSales as $s)
                    <li class="list-group-item d-flex justify-content-between">
                        <a href="{{ route('sales.show', $s) }}">{{ $s->sale_number }}</a>
                        <span>Rp {{ number_format($s->total, 0, ',', '.') }}</span>
                    </li>
                @empty
                    <li class="list-group-item text-muted">Belum ada penjualan.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
