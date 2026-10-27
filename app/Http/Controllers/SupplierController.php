<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
        ];
    }

    public function index()
    {
        $suppliers = Supplier::orderBy('name')->paginate(10);

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        Supplier::create($request->validate($this->rules()));

        return redirect()->route('suppliers.index')->with('success', 'Pemasok berhasil ditambahkan.');
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $supplier->update($request->validate($this->rules()));

        return redirect()->route('suppliers.index')->with('success', 'Pemasok berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchases()->exists()) {
            return redirect()->route('suppliers.index')
                ->with('error', 'Pemasok sudah memiliki pembelian dan tidak dapat dihapus.');
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'Pemasok berhasil dihapus.');
    }
}
