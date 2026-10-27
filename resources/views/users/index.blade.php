@extends('layouts.app')
@section('title', 'Pengguna')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Manajemen Pengguna</h1>
    <a href="{{ route('users.create') }}" class="btn btn-primary">+ Tambah Pengguna</a>
</div>
<div class="card">
    <table class="table table-striped mb-0 align-middle">
        <thead><tr><th>Nama</th><th>Email</th><th>Role</th><th class="text-end">Aksi</th></tr></thead>
        <tbody>
        @foreach ($users as $u)
            <tr>
                <td>{{ $u->name }}</td>
                <td>{{ $u->email }}</td>
                <td><span class="badge {{ $u->role === 'admin' ? 'bg-dark' : 'bg-secondary' }}">{{ $u->role }}</span></td>
                <td class="text-end"><a href="{{ route('users.edit', $u) }}" class="btn btn-sm btn-outline-primary">Edit</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $users->links() }}</div>
@endsection
