<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // otorisasi ditangani middleware "can:admin" pada route
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        $rules = [
            'category_id' => ['required', 'exists:categories,id'],
            'sku' => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($productId)],
            'name' => ['required', 'string', 'max:150'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
        ];

        // Stok awal hanya saat membuat produk; selanjutnya stok berubah lewat transaksi.
        if ($this->isMethod('POST')) {
            $rules['stock'] = ['required', 'integer', 'min:0'];
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'category_id' => 'kategori',
            'sku' => 'SKU',
            'name' => 'nama produk',
            'purchase_price' => 'harga beli',
            'selling_price' => 'harga jual',
            'minimum_stock' => 'stok minimum',
            'stock' => 'stok',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'unique' => ':attribute sudah digunakan.',
            'numeric' => ':attribute harus berupa angka.',
            'integer' => ':attribute harus berupa bilangan bulat.',
            'min' => ':attribute tidak boleh kurang dari :min.',
            'exists' => ':attribute tidak valid.',
        ];
    }
}
