<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductionLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'product_id'      => ['required', 'exists:products,id'],
            'production_date' => ['required', 'date', 'before_or_equal:today'],
            'shift1_qty'      => ['required', 'integer', 'min:0', 'max:9999'],
            'shift2_qty'      => ['required', 'integer', 'min:0', 'max:9999'],
            'shift3_qty'      => ['required', 'integer', 'min:0', 'max:9999'],
            'notes'           => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required'       => 'Produk wajib dipilih.',
            'product_id.exists'         => 'Produk tidak valid.',
            'production_date.required'  => 'Tanggal produksi wajib diisi.',
            'production_date.date'      => 'Format tanggal tidak valid.',
            'production_date.before_or_equal' => 'Tanggal produksi tidak boleh melebihi hari ini.',
            'shift1_qty.required'       => 'Jumlah Shift 1 wajib diisi.',
            'shift1_qty.integer'        => 'Jumlah Shift 1 harus berupa angka.',
            'shift1_qty.min'            => 'Jumlah Shift 1 minimal 0.',
            'shift2_qty.required'       => 'Jumlah Shift 2 wajib diisi.',
            'shift2_qty.integer'        => 'Jumlah Shift 2 harus berupa angka.',
            'shift2_qty.min'            => 'Jumlah Shift 2 minimal 0.',
            'shift3_qty.required'       => 'Jumlah Shift 3 wajib diisi.',
            'shift3_qty.integer'        => 'Jumlah Shift 3 harus berupa angka.',
            'shift3_qty.min'            => 'Jumlah Shift 3 minimal 0.',
        ];
    }
}
