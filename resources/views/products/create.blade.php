@extends('layouts.app')
@section('title', 'Tambah Produk')

@section('content')
<h1 class="h4 mb-3">Tambah Produk</h1>
<div class="card"><div class="card-body">
    <form action="{{ route('products.store') }}" method="POST" novalidate>
        @include('products._form')
    </form>
</div></div>
@endsection
