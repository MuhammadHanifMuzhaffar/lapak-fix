<?php 
  
declare(strict_types=1); 
  
namespace App\Http\Requests; 
  
use Illuminate\Foundation\Http\FormRequest; 
use Illuminate\Validation\Rule; 
  
/** 
 * Checkout hanya menerima SATU hal dari klien: alamat mana yang dipakai. 
 * Isi keranjang, harga, ongkir, dan total seluruhnya dibaca server dari basis data (LP-6). 
 */ 
final class CheckoutRequest extends FormRequest 
{ 
    public function authorize(): bool 
    { 
        return true; 
    } 
  
    public function rules(): array 
    { 
        return [ 
            'alamat_id' => [ 
                'required', 'integer', 
                // Alamat orang lain dan alamat yang tidak ada dijawab dengan pesan yang sama. 
                Rule::exists('alamat', 'id')->where('pengguna_id', $this->user()->id), 
            ], 
        ]; 
    } 
  
    public function messages(): array 
    { 
        return ['alamat_id.exists' => 'Alamat tidak ditemukan.']; 
    } 
}