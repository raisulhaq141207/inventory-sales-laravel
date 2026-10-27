@csrf
<div class="mb-3">
    <label class="form-label">Nama Kategori</label>
    <input type="text" name="name" value="{{ old('name', $category->name ?? '') }}"
           class="form-control @error('name') is-invalid @enderror">
    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
<button class="btn btn-primary">Simpan</button>
<a href="{{ route('categories.index') }}" class="btn btn-link">Batal</a>
