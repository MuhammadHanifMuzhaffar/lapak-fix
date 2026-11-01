<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\PenjagaStok;
use Illuminate\Foundation\Http\FormRequest;

/** Satu-satunya masukan: berapa stok bertambah (positif) atau berkurang (negatif). */
final class PenyesuaianStokRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $batas = PenjagaStok::MAKSIMUM;

        return [
            'selisih' => ['required', 'integer', 'not_in:0', "between:-{$batas},{$batas}"],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'selisih.not_in' => 'Selisih tidak boleh nol.',
        ];
    }
}
