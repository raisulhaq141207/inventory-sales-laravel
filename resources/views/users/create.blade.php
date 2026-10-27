@extends('layouts.app')
@section('title', 'Tambah Pengguna')

@section('content')
<h1 class="h4 mb-3">Tambah Pengguna</h1>
<div class="card"><div class="card-body">
    <form action="{{ route('users.store') }}" method="POST">
        @include('users._form')
    </form>
</div></div>
@endsection
