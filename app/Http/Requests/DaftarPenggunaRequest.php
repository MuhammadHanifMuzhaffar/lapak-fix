<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\NamaPeran;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class DaftarPenggunaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'peran' => ['sometimes', Rule::enum(NamaPeran::class)],
            'per_halaman' => ['sometimes', 'integer', 'between:1,100'],
        ];
    }
}
