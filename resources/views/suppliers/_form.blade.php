@csrf
<div class="mb-3">
    <label class="form-label">Nama Pemasok</label>
    <input type="text" name="name" value="{{ old('name', $supplier->name ?? '') }}"
           class="form-control @error('name') is-invalid @enderror">
    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Telepon</label>
        <input type="text" name="phone" value="{{ old('phone', $supplier->phone ?? '') }}"
               class="form-control @error('phone') is-invalid @enderror">
        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" value="{{ old('email', $supplier->email ?? '') }}"
               class="form-control @error('email') is-invalid @enderror">
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
<button class="btn btn-primary">Simpan</button>
<a href="{{ route('suppliers.index') }}" class="btn btn-link">Batal</a>
