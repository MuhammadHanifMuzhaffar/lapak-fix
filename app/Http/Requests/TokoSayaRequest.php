<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class TokoSayaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $wajib = $this->isMethod('POST') ? 'required' : 'sometimes';

        return [
            'nama' => [$wajib, 'string', 'min:3', 'max:80'],
            'kota' => [$wajib, 'string', 'min:2', 'max:60'],
            'deskripsi' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'ongkir_rupiah' => [$wajib, 'integer', 'between:0,500000'],
        ];
    }
}
