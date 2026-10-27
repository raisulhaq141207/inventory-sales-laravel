@extends('layouts.app')
@section('title', 'Penjualan Baru')

@section('content')
<h1 class="h4 mb-3">Penjualan Baru</h1>

@foreach ($errors->get('items') as $message)
    <div class="alert alert-danger">{{ $message }}</div>
@endforeach
@foreach ($errors->get('items.*') as $messages)
    @foreach ($messages as $message)
        <div class="alert alert-danger">{{ $message }}</div>
    @endforeach
@endforeach

<form action="{{ route('sales.store') }}" method="POST" novalidate>
    @csrf
    <div class="card mb-3">
        <div class="card-body">
            <table class="table align-middle mb-2">
                <thead><tr><th>Produk</th><th style="width:140px">Kuantitas</th><th style="width:60px"></th></tr></thead>
                <tbody id="items-body"></tbody>
            </table>
            <button type="button" id="add-row" class="btn btn-outline-secondary btn-sm">+ Tambah item</button>
            <div class="form-text mt-2">Harga diambil dari database dan total dihitung oleh server.</div>
        </div>
    </div>
    <button class="btn btn-primary">Simpan Penjualan</button>
    <a href="{{ route('sales.index') }}" class="btn btn-link">Batal</a>
</form>
@endsection

@push('scripts')
<script>
    const products = @json($products->map(fn ($p) => ['id' => $p->id, 'label' => $p->sku . ' - ' . $p->name . ' (stok ' . $p->stock . ')'])->values());
    const initial = @json(old('items', [['product_id' => '', 'quantity' => 1]]));
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

        const tdDel = document.createElement('td');
        const del = document.createElement('button');
        del.type = 'button';
        del.className = 'btn btn-sm btn-outline-danger';
        del.textContent = 'x';
        del.onclick = () => tr.remove();
        tdDel.appendChild(del);

        tr.append(tdProduct, tdQty, tdDel);
        body.appendChild(tr);
        n++;
    }

    Object.values(initial).forEach(addRow);
    document.getElementById('add-row').onclick = () => addRow({ product_id: '', quantity: 1 });
</script>
@endpush
