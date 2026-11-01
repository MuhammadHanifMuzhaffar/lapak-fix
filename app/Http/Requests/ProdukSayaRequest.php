<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\StatusProduk;
use App\Services\PenjagaStok;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ProdukSayaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $wajib = $this->isMethod('POST') ? 'required' : 'sometimes';

        return [
            'nama' => [$wajib, 'string', 'max:120'],
            'kategori_id' => [$wajib, 'integer', 'exists:kategori,id'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'harga_rupiah' => [$wajib, 'integer', 'between:100,1000000000'],
            'stok' => $this->isMethod('POST')
                ? ['required', 'integer', 'between:0,'.PenjagaStok::MAKSIMUM]
                : ['prohibited'],
            'berat_gram' => ['sometimes', 'integer', 'between:0,100000'],
            'status' => ['sometimes', Rule::enum(StatusProduk::class)],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'stok.prohibited' => 'Ubah stok dengan selisih lewat POST /toko-saya/produk/{id}/stok.',
        ];
    }
}
