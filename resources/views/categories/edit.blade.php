@extends('layouts.app')
@section('title', 'Edit Kategori')

@section('content')
<h1 class="h4 mb-3">Edit Kategori</h1>
<div class="card"><div class="card-body">
    <form action="{{ route('categories.update', $category) }}" method="POST">
        @method('PUT')
        @include('categories._form')
    </form>
</div></div>
@endsection
