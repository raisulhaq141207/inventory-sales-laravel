@extends('layouts.app')
@section('title', 'Pesanan Saya')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Pesanan Saya</h1>
    <a href="{{ route('my-orders.create') }}" class="btn btn-primary">+ Buat Pesanan</a>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-striped mb-0 align-middle">
            <thead><tr><th>No. Pesanan</th><th>Tanggal</th><th class="text-end">Total</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($orders as $o)
                <tr>
                    <td>{{ $o->order_number }}</td>
                    <td>{{ $o->ordered_at->format('d M Y H:i') }}</td>
                    <td class="text-end">Rp {{ number_format($o->total, 0, ',', '.') }}</td>
                    <td>
                        <span class="badge {{ $o->status === 'confirmed' ? 'bg-success' : ($o->status === 'pending' ? 'bg-warning text-dark' : 'bg-secondary') }}">{{ $o->status }}</span>
                    </td>
                    <td class="text-end"><a href="{{ route('my-orders.show', $o) }}" class="btn btn-sm btn-outline-secondary">Detail</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada pesanan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $orders->links() }}</div>
@endsection
