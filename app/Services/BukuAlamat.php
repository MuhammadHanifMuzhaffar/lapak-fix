<?php 
  
declare(strict_types=1); 
  
namespace App\Services; 
  
use App\Exceptions\GalatDomain; 
use App\Models\Alamat; 
use App\Models\Pengguna; 
use Closure; 
use Illuminate\Support\Collection; 
use Illuminate\Support\Facades\DB; 
  
/** 
 * Satu-satunya pintu perubahan alamat. Menjaga dua aturan yang tidak dapat dijaga per baris: 
 *   - pengguna yang memiliki alamat selalu memiliki TEPAT SATU alamat utama, dan 
 *   - jumlah alamat tidak melebihi batas. 
 * 
 * Keduanya adalah keputusan atas SEKUMPULAN baris. Dua permintaan yang datang bersamaan dapat 
 * sama-sama melihat "belum ada alamat" lalu sama-sama menjadikan alamatnya utama. Maka setiap 
 * perubahan mengunci baris PENGGUNA lebih dulu: gembok induk yang selalu ada, bahkan ketika 
 * pengguna itu belum memiliki alamat satu pun untuk dikunci. 
 */ 
final class BukuAlamat 
{ 
    /** 
     * @param  array<string, string>  $data  hasil validasi AlamatRequest 
     */ 
    public function tambah(Pengguna $pemilik, array $data): Alamat 
    { 
        return $this->terkunci($pemilik, function (Collection $punya) use ($pemilik, $data) { 
            if ($punya->count() >= config('lapak.transaksi.alamat_maksimum')) { 
                throw new GalatDomain('ALAMAT_PENUH', 'Jumlah alamat sudah mencapai batas.', 422); 
            } 
  
            $alamat = new Alamat($data); 
            $alamat->pengguna_id = $pemilik->id; 
            // Alamat pertama otomatis menjadi alamat utama. 
            $alamat->utama = $punya->isEmpty(); 
            $alamat->save(); 
  
            return $alamat; 
        }); 
    } 
  
    public function jadikanUtama(Pengguna $pemilik, int $idAlamat): Alamat 

    { 
        return $this->terkunci($pemilik, function (Collection $punya) use ($idAlamat): Alamat { 
            // Dicari di antara alamat MILIK pengguna ini. Nomor alamat orang lain tidak ditemukan. 
            $terpilih = $punya->firstWhere('id', $idAlamat) ?? abort(404); 
  
            foreach ($punya as $alamat) { 
                $alamat->forceFill(['utama' => $alamat->is($terpilih)])->save(); 
            } 
  
            return $terpilih; 
        }); 
    } 
  
    public function hapus(Pengguna $pemilik, int $idAlamat): void 
    { 
        $this->terkunci($pemilik, function (Collection $punya) use ($idAlamat): void { 
            $dihapus = $punya->firstWhere('id', $idAlamat) ?? abort(404); 
            $dihapus->delete(); 
  
            // Alamat utama yang dihapus digantikan alamat tertua yang tersisa. 
            $pengganti = $punya->first(fn (Alamat $alamat) => ! $alamat->is($dihapus)); 
  
            if ($dihapus->utama && $pengganti !== null) { 
                $pengganti->forceFill(['utama' => true])->save(); 
            } 
        }); 
    } 
  
    /** 
     * @template T 
     * 
     * @param  Closure(Collection<int, Alamat>): T  $kerja  menerima alamat pemilik, urut nomor 
     * @return T 
     */ 
    private function terkunci(Pengguna $pemilik, Closure $kerja): mixed 
    { 
        return DB::transaction(function () use ($pemilik, $kerja) { 
            Pengguna::query()->whereKey($pemilik->id)->lockForUpdate()->firstOrFail(); 
  
            // Dibaca SETELAH gembok didapat, dan dengan kunci: pembacaan biasa di dalam transaksi 
            // MySQL dapat mengembalikan keadaan sebelum permintaan lain selesai menulis. 
            $punya = Alamat::query()->milik($pemilik)->orderBy('id')->lockForUpdate()->get(); 
  
            return $kerja($punya); 
        }); 
    } 
}