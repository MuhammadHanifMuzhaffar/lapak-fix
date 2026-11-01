<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\StatusToko;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class DaftarTokoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', Rule::enum(StatusToko::class)],
            'per_halaman' => ['sometimes', 'integer', 'between:1,100'],
        ];
    }
}
