@extends('layouts.app')
@section('title', 'Pembelian Baru')

@section('content')
<h1 class="h4 mb-3">Pembelian Baru (Stock-in)</h1>

@foreach ($errors->get('items') as $message)
    <div class="alert alert-danger">{{ $message }}</div>
@endforeach
@foreach ($errors->get('items.*') as $messages)
    @foreach ($messages as $message)
        <div class="alert alert-danger">{{ $message }}</div>
    @endforeach
@endforeach

<form action="{{ route('purchases.store') }}" method="POST" novalidate>
    @csrf
    <div class="mb-3">
        <label class="form-label">Pemasok</label>
        <select name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror">
            <option value="">-- Pilih pemasok --</option>
            @foreach ($suppliers as $s)
                <option value="{{ $s->id }}" @selected(old('supplier_id') == $s->id)>{{ $s->name }}</option>
            @endforeach
        </select>
        @error('supplier_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <table class="table align-middle mb-2">
                <thead><tr><th>Produk</th><th style="width:130px">Kuantitas</th><th style="width:170px">Harga Beli (Rp)</th><th style="width:60px"></th></tr></thead>
                <tbody id="items-body"></tbody>
            </table>
            <button type="button" id="add-row" class="btn btn-outline-secondary btn-sm">+ Tambah item</button>
        </div>
    </div>
    <button class="btn btn-primary">Simpan Pembelian</button>
    <a href="{{ route('purchases.index') }}" class="btn btn-link">Batal</a>
</form>
@endsection

@push('scripts')
@php
    $productsJs = $products->map(function ($p) {
        return ['id' => $p->id, 'label' => $p->sku . ' - ' . $p->name, 'cost' => $p->purchase_price];
    })->values();

    $initialItems = old('items', [['product_id' => '', 'quantity' => 1, 'unit_cost' => '']]);
@endphp
<script>
    const products = @json($productsJs);
    const initial = @json($initialItems);
    const body = document.getElementById('items-body');
    let n = 0;

    function addRow(item) {
        const tr = document.createElement('tr');

        const tdProduct = document.createElement('td');
        const sel = document.createElement('select');
        sel.name = 'items[' + n + '][product_id]';
        sel.className = 'form-select';
        sel.add(new Option('-- Pilih produk --', ''));
        products.forEach(p => sel.add(new Option(p.label, p.id, false, String(p.id) === String(item.product_id))));
        tdProduct.appendChild(sel);

        const tdQty = document.createElement('td');
        const qty = document.createElement('input');
        qty.type = 'number';
        qty.name = 'items[' + n + '][quantity]';
        qty.value = item.quantity ?? 1;
        qty.className = 'form-control';
        tdQty.appendChild(qty);

        const tdCost = document.createElement('td');
        const cost = document.createElement('input');
        cost.type = 'number';
        cost.step = '0.01';
        cost.name = 'items[' + n + '][unit_cost]';
        cost.value = item.unit_cost ?? '';
        cost.className = 'form-control';
        tdCost.appendChild(cost);

        // Isi otomatis harga beli dari data produk (masih bisa diubah).
        sel.onchange = () => {
            const p = products.find(x => String(x.id) === sel.value);
            if (p && cost.value === '') cost.value = p.cost;
        };

        const tdDel = document.createElement('td');
        const del = document.createElement('button');
        del.type = 'button';
        del.className = 'btn btn-sm btn-outline-danger';
        del.textContent = 'x';
        del.onclick = () => tr.remove();
        tdDel.appendChild(del);

        tr.append(tdProduct, tdQty, tdCost, tdDel);
        body.appendChild(tr);
        n++;
    }

    Object.values(initial).forEach(addRow);
    document.getElementById('add-row').onclick = () => addRow({ product_id: '', quantity: 1, unit_cost: '' });
</script>
@endpush
