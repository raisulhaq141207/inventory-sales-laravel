@extends('layouts.app')
@section('title', 'Detail Pembelian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Pembelian {{ $purchase->purchase_number }}</h1>
    <span class="badge fs-6 {{ $purchase->status === 'completed' ? 'bg-success' : 'bg-secondary' }}">{{ $purchase->status }}</span>
</div>

<div class="card mb-3"><div class="card-body">
    <dl class="row mb-0">
        <dt class="col-sm-3">Tanggal</dt><dd class="col-sm-9">{{ $purchase->purchased_at->format('d M Y H:i') }}</dd>
        <dt class="col-sm-3">Pemasok</dt><dd class="col-sm-9">{{ $purchase->supplier->name }}</dd>
        <dt class="col-sm-3">Petugas</dt><dd class="col-sm-9">{{ $purchase->user->name }}</dd>
        <dt class="col-sm-3">Total</dt><dd class="col-sm-9 fw-semibold">Rp {{ number_format($purchase->total, 0, ',', '.') }}</dd>
    </dl>
</div></div>

<div class="card mb-3">
    <table class="table mb-0">
        <thead><tr><th>Produk</th><th class="text-end">Harga Beli</th><th class="text-end">Qty</th><th class="text-end">Subtotal</th></tr></thead>
        <tbody>
        @foreach ($purchase->items as $item)
            <tr>
                <td>{{ $item->product->sku }} - {{ $item->product->name }}</td>
                <td class="text-end">Rp {{ number_format($item->unit_cost, 0, ',', '.') }}</td>
                <td class="text-end">{{ $item->quantity }}</td>
                <td class="text-end">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<a href="{{ route('purchases.index') }}" class="btn btn-link">&larr; Kembali</a>
@can('admin')
    @if ($purchase->status === 'completed')
        <form action="{{ route('purchases.cancel', $purchase) }}" method="POST" class="d-inline"
              onsubmit="return confirm('Batalkan pembelian ini? Stok akan dikurangi kembali.')">
            @csrf
            <button class="btn btn-outline-danger">Batalkan Pembelian</button>
        </form>
    @endif
@endcan
@endsection
