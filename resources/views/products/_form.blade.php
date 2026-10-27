@csrf
<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">SKU</label>
        <input type="text" name="sku" value="{{ old('sku', $product->sku ?? '') }}"
               class="form-control @error('sku') is-invalid @enderror">
        @error('sku') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-8 mb-3">
        <label class="form-label">Nama Produk</label>
        <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}"
               class="form-control @error('name') is-invalid @enderror">
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Kategori</label>
    <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
        <option value="">-- Pilih kategori --</option>
        @foreach ($categories as $c)
            <option value="{{ $c->id }}" @selected(old('category_id', $product->category_id ?? null) == $c->id)>{{ $c->name }}</option>
        @endforeach
    </select>
    @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Harga Beli (Rp)</label>
        <input type="number" step="0.01" name="purchase_price"
               value="{{ old('purchase_price', $product->purchase_price ?? '') }}"
               class="form-control @error('purchase_price') is-invalid @enderror">
        @error('purchase_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Harga Jual (Rp)</label>
        <input type="number" step="0.01" name="selling_price"
               value="{{ old('selling_price', $product->selling_price ?? '') }}"
               class="form-control @error('selling_price') is-invalid @enderror">
        @error('selling_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="row">
    @if (! isset($product))
        <div class="col-md-6 mb-3">
            <label class="form-label">Stok Awal</label>
            <input type="number" name="stock" value="{{ old('stock', 0) }}"
                   class="form-control @error('stock') is-invalid @enderror">
            @error('stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    @else
        <div class="col-md-6 mb-3">
            <label class="form-label">Stok Saat Ini</label>
            <input type="text" value="{{ $product->stock }}" class="form-control" disabled>
            <div class="form-text">Stok hanya berubah lewat transaksi pembelian/penjualan.</div>
        </div>
    @endif
    <div class="col-md-6 mb-3">
        <label class="form-label">Stok Minimum</label>
        <input type="number" name="minimum_stock"
               value="{{ old('minimum_stock', $product->minimum_stock ?? 0) }}"
               class="form-control @error('minimum_stock') is-invalid @enderror">
        @error('minimum_stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<button class="btn btn-primary">Simpan</button>
<a href="{{ route('products.index') }}" class="btn btn-link">Batal</a>
