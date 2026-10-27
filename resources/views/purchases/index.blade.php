@extends('layouts.app')
@section('title', 'Pembelian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Daftar Pembelian (Stock-in)</h1>
    <a href="{{ route('purchases.create') }}" class="btn btn-primary">+ Pembelian Baru</a>
</div>

<form method="GET" action="{{ route('purchases.index') }}" class="row g-2 mb-3">
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
        <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">Reset</a>
    </div>
</form>
@foreach (['q', 'status', 'from', 'to'] as $f)
    @error($f) <div class="text-danger small">{{ $message }}</div> @enderror
@endforeach

<div class="card">
    <div class="table-responsive">
        <table class="table table-striped mb-0 align-middle">
            <thead><tr><th>No. Transaksi</th><th>Tanggal</th><th>Pemasok</th><th>Petugas</th><th class="text-end">Total</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($purchases as $p)
                <tr>
                    <td>{{ $p->purchase_number }}</td>
                    <td>{{ $p->purchased_at->format('d M Y H:i') }}</td>
                    <td>{{ $p->supplier->name }}</td>
                    <td>{{ $p->user->name }}</td>
                    <td class="text-end">Rp {{ number_format($p->total, 0, ',', '.') }}</td>
                    <td><span class="badge {{ $p->status === 'completed' ? 'bg-success' : 'bg-secondary' }}">{{ $p->status }}</span></td>
                    <td class="text-end"><a href="{{ route('purchases.show', $p) }}" class="btn btn-sm btn-outline-secondary">Detail</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada pembelian.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $purchases->links() }}</div>
@endsection
