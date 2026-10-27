@extends('layouts.app')
@section('title', 'Tambah Kategori')

@section('content')
<h1 class="h4 mb-3">Tambah Kategori</h1>
<div class="card"><div class="card-body">
    <form action="{{ route('categories.store') }}" method="POST">
        @include('categories._form')
    </form>
</div></div>
@endsection
