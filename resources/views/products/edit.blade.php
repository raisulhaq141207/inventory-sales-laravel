@extends('layouts.app')
@section('title', 'Edit Produk')

@section('content')
<h1 class="h4 mb-3">Edit Produk</h1>
<div class="card"><div class="card-body">
    <form action="{{ route('products.update', $product) }}" method="POST" novalidate>
        @method('PUT')
        @include('products._form')
    </form>
</div></div>
@endsection
