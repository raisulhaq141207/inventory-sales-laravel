@extends('layouts.app')
@section('title', 'Edit Pengguna')

@section('content')
<h1 class="h4 mb-3">Edit Pengguna</h1>
<div class="card"><div class="card-body">
    <form action="{{ route('users.update', $user) }}" method="POST">
        @method('PUT')
        @include('users._form')
    </form>
</div></div>
@endsection
