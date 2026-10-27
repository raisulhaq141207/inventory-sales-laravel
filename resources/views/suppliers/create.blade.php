@extends('layouts.app')
@section('title', 'Tambah Pemasok')

@section('content')
<h1 class="h4 mb-3">Tambah Pemasok</h1>
<div class="card"><div class="card-body">
    <form action="{{ route('suppliers.store') }}" method="POST">
        @include('suppliers._form')
    </form>
</div></div>
@endsection
