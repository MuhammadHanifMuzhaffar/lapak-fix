<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SimulasiBelanjaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $batas = config('lapak.belanja');

        return [
            'butir' => ['required', 'array', 'min:1', 'max:'.$batas['butir_maksimum']],
            'butir.*.produk_id' => ['required', 'integer', 'min:1', 'distinct'],
            'butir.*.kuantitas' => ['required', 'integer', 'between:1,'.$batas['kuantitas_maksimum']],
        ];
    }

    /**
     * @return list<array{produk_id: int, kuantitas: int}>
     */
    public function butir(): array
    {
        return array_map(
            fn (array $butir) => [
                'produk_id' => (int) $butir['produk_id'],
                'kuantitas' => (int) $butir['kuantitas'],
            ],
            $this->validated('butir'),
        );
    }
}
