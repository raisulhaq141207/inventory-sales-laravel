@extends('layouts.app')
@section('title', 'Pesanan Online')

@section('content')
<h1 class="h4 mb-3">Pesanan Online</h1>

<form method="GET" action="{{ route('orders.index') }}" class="row g-2 mb-3">
    <div class="col-md-4"><input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Nomor pesanan"></div>
    <div class="col-md-3">
        <select name="status" class="form-select">
            <option value="">Semua status</option>
            <option value="pending" @selected(request('status') === 'pending')>Pending</option>
            <option value="confirmed" @selected(request('status') === 'confirmed')>Confirmed</option>
            <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
        </select>
    </div>
    <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-secondary">Filter</button>
        <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">Reset</a>
    </div>
</form>
@error('status') <div class="text-danger small mb-2">{{ $message }}</div> @enderror

<div class="card">
    <div class="table-responsive">
        <table class="table table-striped mb-0 align-middle">
            <thead><tr><th>No. Pesanan</th><th>Tanggal</th><th>Customer</th><th class="text-end">Total</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($orders as $o)
                <tr>
                    <td>{{ $o->order_number }}</td>
                    <td>{{ $o->ordered_at->format('d M Y H:i') }}</td>
                    <td>{{ $o->user->name }}</td>
                    <td class="text-end">Rp {{ number_format($o->total, 0, ',', '.') }}</td>
                    <td>
                        <span class="badge {{ $o->status === 'confirmed' ? 'bg-success' : ($o->status === 'pending' ? 'bg-warning text-dark' : 'bg-secondary') }}">{{ $o->status }}</span>
                    </td>
                    <td class="text-end"><a href="{{ route('orders.show', $o) }}" class="btn btn-sm btn-outline-secondary">Detail</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada pesanan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $orders->links() }}</div>
@endsection
