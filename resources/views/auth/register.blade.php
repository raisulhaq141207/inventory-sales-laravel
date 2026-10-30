@extends('layouts.app')
@section('title', 'Daftar')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <div class="text-center mb-3">
                    <img src="{{ asset('images/logo.svg') }}" alt="Logo Inventory &amp; Sales" width="64" height="64">
                    <div class="fw-semibold mt-2">Inventory &amp; Sales</div>
                </div>
                <h1 class="h4 mb-3">Daftar Customer</h1>
                <form action="{{ route('register.attempt') }}" method="POST" novalidate>
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               class="form-control @error('name') is-invalid @enderror" autofocus>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="form-control @error('email') is-invalid @enderror">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password (min. 8 karakter)</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ulangi Password</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>
                    <button class="btn btn-primary w-100">Daftar</button>
                </form>
                <div class="text-center mt-3 small">
                    Sudah punya akun? <a href="{{ route('login') }}">Login</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
