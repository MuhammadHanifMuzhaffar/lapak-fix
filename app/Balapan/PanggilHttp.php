<?php 
  
declare(strict_types=1); 
  
namespace App\Balapan; 
  
use Illuminate\Contracts\Http\Kernel; 
use Illuminate\Http\Request; 
  
/** 
 * Memanggil API dari dalam proses anak TANPA server web: permintaan diserahkan langsung ke kernel 
 * HTTP, sehingga tetap melewati seluruh middleware, sama seperti permintaan dari klien. 
 */ 
trait PanggilHttp 
{ 
    /** 
     * Mengembalikan status, badan JSON, dan header respons. 
     * 
     * @param  array<string, mixed>  $badan 
     * @param  array<string, string>  $header 
     * @return array{0: int, 1: array<string, mixed>, 2: array<string, list<string|null>>} 
     */ 
    protected function panggil( 
        string $metode, 
        string $alamat, 
        array $badan = [], 
        array $header = [], 
    ): array { 
        $server = ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json']; 
  
        foreach ($header as $nama => $nilai) { 
            $server['HTTP_'.strtoupper(str_replace('-', '_', $nama))] = $nilai; 
        } 
  
        $isi = $badan === [] ? null : json_encode($badan, JSON_THROW_ON_ERROR); 
        $permintaan = Request::create($alamat, $metode, [], [], [], $server, $isi); 
        $respons = app(Kernel::class)->handle($permintaan); 
  
        return [ 
            $respons->getStatusCode(), 
            json_decode((string) $respons->getContent(), true) ?? [], 
            $respons->headers->all(), 
        ]; 
    } 
  
    /** "201", atau "409 LP-4" bila respons membawa kode galat. */ 
    protected function ringkas(int $status, array $json): string 
    { 
        $kode = $json['galat']['kode'] ?? null; 
  
        return $kode === null ? (string) $status : "{$status} {$kode}"; 
    } 
}