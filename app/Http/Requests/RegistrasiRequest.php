<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class RegistrasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Surel dinormalkan agar huruf besar/kecil dianggap akun yang sama. */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('surel'))) {
            $this->merge(['surel' => mb_strtolower(trim($this->input('surel')))]);
        }
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'min:3', 'max:100'],
            'surel' => ['required', 'string', 'email:rfc', 'max:150', Rule::unique('pengguna', 'surel')],
            'telepon' => ['nullable', 'string', 'regex:/^08[0-9]{8,11}$/'],
            'kata_sandi' => ['required', 'string', 'max:72', Password::min(10)->letters()->numbers()],
            'konfirmasi_kata_sandi' => ['required', 'same:kata_sandi'],
        ];
    }

    public function messages(): array
    {
        return [
            'surel.unique' => 'Surel sudah terdaftar.',
            'telepon.regex' => 'Nomor telepon harus berawalan 08 dan terdiri atas 10 sampai 13 angka.',
            'konfirmasi_kata_sandi.same' => 'Konfirmasi kata sandi tidak cocok.',
        ];
    }
}
