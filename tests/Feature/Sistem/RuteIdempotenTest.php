<?php 
  
use Illuminate\Support\Facades\Route; 
  
/* 
| LP-11, ditegakkan dengan cara yang sama seperti RUTE_PUBLIK dan matriks otorisasi. 
| 
| Setiap rute POST di bawah /api/v1 WAJIB tercatat pada salah satu dari dua daftar ini. POST 
| adalah satu-satunya metode yang tidak idempoten menurut HTTP: mengulanginya dapat berefek dua 
| kali. Menambah rute POST karenanya menuntut satu keputusan tertulis: dijaga Idempotency-Key, 
| atau aman diulang karena sebab yang disebutkan. 
*/ 
const POST_BERKUNCI = [ 
    'POST api/v1/checkout', 
    'POST api/v1/toko-saya/produk/{id}/stok', 
]; 
  
const POST_TANPA_KUNCI = [ 
    'POST api/v1/registrasi' => 'Surel unik: pengulangan dijawab 422, tidak membuat akun kedua.', 
    'POST api/v1/masuk' => 'Pengulangan hanya menerbitkan sesi tambahan yang dapat dicabut.', 
    'POST api/v1/keluar' => 'Mencabut sesi yang sudah dicabut tidak mengubah apa pun.', 
    'POST api/v1/sesi/konfirmasi' => 'Kunci lama mati setelah berhasil; pengulangan dijawab 401.', 
    'POST api/v1/simulasi-belanja' => 'Hanya menghitung, tidak menulis.', 
    'POST api/v1/toko-saya' => 'Satu pengguna satu toko (LP-3): pengulangan dijawab 409.', 
    'POST api/v1/toko-saya/produk' => 'Tidak menyentuh uang; produk kembar dapat diarsipkan.', 
    'POST api/v1/admin/toko/{id}/setujui' => 'UPDATE bersyarat: pengulangan dijawab 409.', 
    'POST api/v1/admin/toko/{id}/tangguhkan' => 'UPDATE bersyarat: pengulangan dijawab 409.', 
    'POST api/v1/alamat' => 'Tidak menyentuh uang maupun stok; jumlah alamat dibatasi.', 
    'POST api/v1/webhook/pembayaran' => 'Dijaga status tagihan di bawah gembok checkout.', 
]; 
  
/** @return array<string, list<string>> "POST alamat" => middleware rute itu */ 
function rutePost(): array 
{ 
    $daftar = []; 
  
    foreach (Route::getRoutes() as $rute) { 
        if (str_starts_with($rute->uri(), 'api/v1') && in_array('POST', $rute->methods(), true)) { 
            $daftar['POST '.$rute->uri()] = array_values( 
                array_filter($rute->gatherMiddleware(), 'is_string'), 
            ); 
        } 
    } 
  
    return $daftar; 
} 
  
it('mencatat setiap rute POST pada tepat satu daftar', function () { 
    $tercatat = [...POST_BERKUNCI, ...array_keys(POST_TANPA_KUNCI)]; 
    $rute = array_keys(rutePost()); 
  
    $belum = array_values(array_diff($rute, $tercatat)); 
    $usang = array_values(array_diff($tercatat, $rute)); 
    $ganda = array_values(array_intersect(POST_BERKUNCI, array_keys(POST_TANPA_KUNCI))); 
  
    expect($belum)->toBeEmpty('Rute POST belum diklasifikasi: '.implode(', ', $belum)) 
        ->and($usang)->toBeEmpty('Tercatat tetapi rutenya tidak ada: '.implode(', ', $usang)) 
        ->and($ganda)->toBeEmpty('Tercatat pada kedua daftar: '.implode(', ', $ganda)); 
}); 
  
it('memasang idempoten pada setiap rute berkunci, di belakang kunci sesi', function () { 
    $salah = []; 
  
    foreach (POST_BERKUNCI as $nama) { 
        $middleware = rutePost()[$nama] ?? []; 
        $auth = array_search('auth:sesi', $middleware, true); 
        $idempoten = array_search('idempoten', $middleware, true); 
  
        // Idempoten mencatat kunci atas nama pengguna, maka harus berjalan setelah auth:sesi. 
        if ($auth === false || $idempoten === false || $idempoten < $auth) { 
            $salah[] = $nama; 
        } 
    } 
  
    expect($salah)->toBeEmpty('Tanpa idempoten atau urutannya salah: '.implode(', ', $salah)); 
}); 
  

it('tidak memasang idempoten pada rute yang dinyatakan tidak memerlukannya', function () { 
    $salah = array_keys(array_filter( 
        array_intersect_key(rutePost(), POST_TANPA_KUNCI), 
        fn (array $middleware) => in_array('idempoten', $middleware, true), 
    )); 
  
    expect($salah)->toBeEmpty('Tanpa kunci tetapi memasang idempoten: '.implode(', ', $salah)); 
}); 
  
it('menuliskan alasan untuk setiap rute tanpa kunci', function () { 
    foreach (POST_TANPA_KUNCI as $nama => $alasan) { 
        expect(mb_strlen($alasan))->toBeGreaterThan(20, "Alasan {$nama} terlalu singkat."); 
    } 
});