<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class KatalogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maksimum = config('lapak.katalog.per_halaman_maksimum');

        return [
            'kategori' => ['sometimes', 'string', 'max:80'],
            'toko' => ['sometimes', 'string', 'max:100'],
            'cari' => ['sometimes', 'string', 'min:2', 'max:60'],
            'harga_min' => ['sometimes', 'integer', 'min:0'],
            'harga_maks' => [
                'sometimes',
                'integer',
                'min:0',
                Rule::when($this->filled('harga_min'), ['gte:harga_min']),
            ],
            'tersedia' => ['sometimes', 'boolean'],
            'urut' => ['sometimes', 'in:terbaru,harga_naik,harga_turun'],
            'per_halaman' => ['sometimes', 'integer', 'between:1,'.$maksimum],
            'halaman' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    public function perHalaman(): int
    {
        return $this->integer('per_halaman', config('lapak.katalog.per_halaman_bawaan'));
    }
}
