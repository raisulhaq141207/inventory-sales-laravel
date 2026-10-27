@extends('layouts.app')
@section('title', 'Edit Pemasok')

@section('content')
<h1 class="h4 mb-3">Edit Pemasok</h1>
<div class="card"><div class="card-body">
    <form action="{{ route('suppliers.update', $supplier) }}" method="POST">
        @method('PUT')
        @include('suppliers._form')
    </form>
</div></div>
@endsection
