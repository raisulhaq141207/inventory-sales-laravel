@extends('layouts.app')
@section('title', 'Detail Pesanan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Pesanan {{ $order->order_number }}</h1>
    <span class="badge fs-6 {{ $order->status === 'confirmed' ? 'bg-success' : ($order->status === 'pending' ? 'bg-warning text-dark' : 'bg-secondary') }}">{{ $order->status }}</span>
</div>

<div class="card mb-3"><div class="card-body">
    <dl class="row mb-0">
        <dt class="col-sm-3">Tanggal</dt><dd class="col-sm-9">{{ $order->ordered_at->format('d M Y H:i') }}</dd>
        <dt class="col-sm-3">Total</dt><dd class="col-sm-9 fw-semibold">Rp {{ number_format($order->total, 0, ',', '.') }}</dd>
    </dl>
</div></div>

<div class="card mb-3">
    <table class="table mb-0">
        <thead><tr><th>Produk</th><th class="text-end">Harga</th><th class="text-end">Jumlah</th><th class="text-end">Subtotal</th></tr></thead>
        <tbody>
        @foreach ($order->items as $item)
            <tr>
                <td>{{ $item->product->name }}</td>
                <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                <td class="text-end">{{ $item->quantity }}</td>
                <td class="text-end">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<a href="{{ route('my-orders.index') }}" class="btn btn-link">&larr; Kembali</a>
@if ($order->status === 'pending')
    <form action="{{ route('my-orders.cancel', $order) }}" method="POST" class="d-inline"
          onsubmit="return confirm('Batalkan pesanan ini?')">
        @csrf
        <button class="btn btn-outline-danger">Batalkan Pesanan</button>
    </form>
@endif
@endsection
