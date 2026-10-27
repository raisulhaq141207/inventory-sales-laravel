@extends('layouts.app')
@section('title', 'Pemasok')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Daftar Pemasok</h1>
    <a href="{{ route('suppliers.create') }}" class="btn btn-primary">+ Tambah Pemasok</a>
</div>
<div class="card">
    <table class="table table-striped mb-0 align-middle">
        <thead><tr><th>Nama</th><th>Telepon</th><th>Email</th><th class="text-end">Aksi</th></tr></thead>
        <tbody>
        @forelse ($suppliers as $s)
            <tr>
                <td>{{ $s->name }}</td>
                <td>{{ $s->phone ?: '-' }}</td>
                <td>{{ $s->email ?: '-' }}</td>
                <td class="text-end">
                    <a href="{{ route('suppliers.edit', $s) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                    <form action="{{ route('suppliers.destroy', $s) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Hapus pemasok ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted py-4">Belum ada pemasok.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $suppliers->links() }}</div>
@endsection
