@extends('layouts.app')
@section('title', 'Detail Penjualan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Penjualan {{ $sale->sale_number }}</h1>
    <span class="badge fs-6 {{ $sale->status === 'completed' ? 'bg-success' : 'bg-secondary' }}">{{ $sale->status }}</span>
</div>

<div class="card mb-3"><div class="card-body">
    <dl class="row mb-0">
        <dt class="col-sm-3">Tanggal</dt><dd class="col-sm-9">{{ $sale->sold_at->format('d M Y H:i') }}</dd>
        <dt class="col-sm-3">Kasir</dt><dd class="col-sm-9">{{ $sale->user->name }}</dd>
        <dt class="col-sm-3">Total</dt><dd class="col-sm-9 fw-semibold">Rp {{ number_format($sale->total, 0, ',', '.') }}</dd>
    </dl>
</div></div>

<div class="card mb-3">
    <table class="table mb-0">
        <thead><tr><th>Produk</th><th class="text-end">Harga</th><th class="text-end">Qty</th><th class="text-end">Subtotal</th></tr></thead>
        <tbody>
        @foreach ($sale->items as $item)
            <tr>
                <td>{{ $item->product->sku }} - {{ $item->product->name }}</td>
                <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                <td class="text-end">{{ $item->quantity }}</td>
                <td class="text-end">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<a href="{{ route('sales.index') }}" class="btn btn-link">&larr; Kembali</a>
@can('admin')
    @if ($sale->status === 'completed')
        <form action="{{ route('sales.cancel', $sale) }}" method="POST" class="d-inline"
              onsubmit="return confirm('Batalkan penjualan ini? Stok akan dikembalikan.')">
            @csrf
            <button class="btn btn-outline-danger">Batalkan Penjualan</button>
        </form>
    @endif
@endcan
@endsection
