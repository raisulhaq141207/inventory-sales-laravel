@extends('layouts.app')
@section('title', 'Detail Pesanan Online')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Pesanan {{ $order->order_number }}</h1>
    <span class="badge fs-6 {{ $order->status === 'confirmed' ? 'bg-success' : ($order->status === 'pending' ? 'bg-warning text-dark' : 'bg-secondary') }}">{{ $order->status }}</span>
</div>

<div class="card mb-3"><div class="card-body">
    <dl class="row mb-0">
        <dt class="col-sm-3">Customer</dt><dd class="col-sm-9">{{ $order->user->name }} ({{ $order->user->email }})</dd>
        <dt class="col-sm-3">Tanggal</dt><dd class="col-sm-9">{{ $order->ordered_at->format('d M Y H:i') }}</dd>
        <dt class="col-sm-3">Total</dt><dd class="col-sm-9 fw-semibold">Rp {{ number_format($order->total, 0, ',', '.') }}</dd>
        @if ($order->sale)
            <dt class="col-sm-3">Penjualan</dt>
            <dd class="col-sm-9"><a href="{{ route('sales.show', $order->sale) }}">{{ $order->sale->sale_number }}</a></dd>
        @endif
    </dl>
</div></div>

<div class="card mb-3">
    <table class="table mb-0">
        <thead><tr><th>Produk</th><th class="text-end">Harga</th><th class="text-end">Jumlah</th><th class="text-end">Subtotal</th></tr></thead>
        <tbody>
        @foreach ($order->items as $item)
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

<a href="{{ route('orders.index') }}" class="btn btn-link">&larr; Kembali</a>
@if ($order->status === 'pending')
    <form action="{{ route('orders.confirm', $order) }}" method="POST" class="d-inline"
          onsubmit="return confirm('Konfirmasi pesanan ini dan catat sebagai penjualan?')">
        @csrf
        <button class="btn btn-success">Konfirmasi Pesanan</button>
    </form>
    <form action="{{ route('orders.cancel', $order) }}" method="POST" class="d-inline"
          onsubmit="return confirm('Batalkan pesanan ini? Stok akan dikembalikan.')">
        @csrf
        <button class="btn btn-outline-danger">Batalkan</button>
    </form>
@endif
@endsection
