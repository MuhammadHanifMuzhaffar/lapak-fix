<?php 
  
declare(strict_types=1); 
  
namespace App\Http\Requests; 
  
use Illuminate\Foundation\Http\FormRequest; 
  
/** Tidak ada pengguna_id maupun utama: keduanya ditentukan server. */ 
final class AlamatRequest extends FormRequest 
{ 
    public function authorize(): bool 
    { 
        return true; 

    } 
  
    public function rules(): array 
    { 
        return [ 
            'label' => ['required', 'string', 'min:2', 'max:30'], 
            'nama_penerima' => ['required', 'string', 'min:3', 'max:80'], 
            'telepon' => ['required', 'string', 'regex:/\A0[0-9]{8,14}\z/'], 
            'jalan' => ['required', 'string', 'min:5', 'max:200'], 
            'kota' => ['required', 'string', 'min:2', 'max:60'], 
            'kode_pos' => ['required', 'string', 'regex:/\A[0-9]{5}\z/'], 
        ]; 
    } 
  
    public function messages(): array 
    { 
        return [ 
            'telepon.regex' => 'Nomor telepon diawali 0 dan berisi 9 sampai 15 angka.', 
            'kode_pos.regex' => 'Kode pos berisi tepat 5 angka.', 
        ]; 
    } 
}