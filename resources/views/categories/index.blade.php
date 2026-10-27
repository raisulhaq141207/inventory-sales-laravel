@extends('layouts.app')
@section('title', 'Kategori')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Daftar Kategori</h1>
    <a href="{{ route('categories.create') }}" class="btn btn-primary">+ Tambah Kategori</a>
</div>
<div class="card">
    <table class="table table-striped mb-0 align-middle">
        <thead><tr><th>Nama</th><th class="text-end">Jumlah Produk</th><th class="text-end">Aksi</th></tr></thead>
        <tbody>
        @forelse ($categories as $c)
            <tr>
                <td>{{ $c->name }}</td>
                <td class="text-end">{{ $c->products_count }}</td>
                <td class="text-end">
                    <a href="{{ route('categories.edit', $c) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                    <form action="{{ route('categories.destroy', $c) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Hapus kategori ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="3" class="text-center text-muted py-4">Belum ada kategori.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $categories->links() }}</div>
@endsection
