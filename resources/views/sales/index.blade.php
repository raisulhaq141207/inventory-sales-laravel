@extends('layouts.app')
@section('title', 'Penjualan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Daftar Penjualan</h1>
    <a href="{{ route('sales.create') }}" class="btn btn-primary">+ Penjualan Baru</a>
</div>

<form method="GET" action="{{ route('sales.index') }}" class="row g-2 mb-3">
    <div class="col-md-3"><input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Nomor transaksi"></div>
    <div class="col-md-2">
        <select name="status" class="form-select">
            <option value="">Semua status</option>
            <option value="completed" @selected(request('status') === 'completed')>Selesai</option>
            <option value="cancelled" @selected(request('status') === 'cancelled')>Dibatalkan</option>
        </select>
    </div>
    <div class="col-md-2"><input type="date" name="from" value="{{ request('from') }}" class="form-control"></div>
    <div class="col-md-2"><input type="date" name="to" value="{{ request('to') }}" class="form-control"></div>
    <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-secondary">Filter</button>
        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">Reset</a>
    </div>
</form>
@foreach (['q', 'status', 'from', 'to'] as $f)
    @error($f) <div class="text-danger small">{{ $message }}</div> @enderror
@endforeach

<div class="card">
    <div class="table-responsive">
        <table class="table table-striped mb-0 align-middle">
            <thead><tr><th>No. Transaksi</th><th>Tanggal</th><th>Kasir</th><th class="text-end">Total</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($sales as $s)
                <tr>
                    <td>{{ $s->sale_number }}</td>
                    <td>{{ $s->sold_at->format('d M Y H:i') }}</td>
                    <td>{{ $s->user->name }}</td>
                    <td class="text-end">Rp {{ number_format($s->total, 0, ',', '.') }}</td>
                    <td><span class="badge {{ $s->status === 'completed' ? 'bg-success' : 'bg-secondary' }}">{{ $s->status }}</span></td>
                    <td class="text-end"><a href="{{ route('sales.show', $s) }}" class="btn btn-sm btn-outline-secondary">Detail</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada penjualan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $sales->links() }}</div>
@endsection
