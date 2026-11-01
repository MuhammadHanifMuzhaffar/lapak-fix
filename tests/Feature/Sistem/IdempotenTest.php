<?php

use App\Exceptions\GalatDomain;
use App\Models\Kategori;
use App\Models\KunciIdempotensi;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/*
| Pintu uji: setiap pemanggilan yang berhasil membuat SATU kategori. Jumlah baris kategori
| karenanya sama dengan jumlah efek yang benar-benar terjadi.
*/
beforeEach(function () {
    Route::middleware(['api', 'auth:sesi', 'idempoten'])
        ->post('/api/_uji/catat', function (Request $permintaan) {
            $kategori = Kategori::create([
                'nama' => $permintaan->input('nama'),
                'slug' => Str::random(12),
            ]);

            if ($permintaan->boolean('gagal')) {

                throw new GalatDomain('UJI', 'Gagal setelah menulis.', 409);
            }
            if ($permintaan->boolean('meledak')) {
                throw new RuntimeException('galat tak terduga');
            }

            return response()->json(['sukses' => true, 'data' => ['id' => $kategori->id]], 201);
        });

    $this->andi = Pengguna::factory()->create();
    $this->kunci = 'kunci-uji-0123456789';
    $this->kirim = fn (array $badan, ?string $kunci = null) => $this->postJson(
        '/api/_uji/catat', $badan, ['Idempotency-Key' => $kunci ?? $this->kunci],
    );
});

it('menolak permintaan tanpa kunci atau dengan kunci yang bentuknya salah (LP-11)', function (
    ?string $kunci,
) {
    $header = $kunci === null ? [] : ['Idempotency-Key' => $kunci];

    $this->sebagai($this->andi)
        ->postJson('/api/_uji/catat', ['nama' => 'Batik'], $header)
        ->assertUnprocessable()
        ->assertJsonPath('galat.kode', 'LP-11');

    expect(Kategori::count())->toBe(0);
})->with([
    'tanpa header' => [null],
    'terlalu pendek' => ['abc123'],
    'terlalu panjang' => [str_repeat('a', 65)],
    'berisi spasi' => ['kunci dengan spasi 123'],
    'berisi baris baru' => ["kunci-uji-0123456789\n"],
]);

it('menjalankan permintaan pertama dan mencatat responsnya', function () {
    $this->sebagai($this->andi);

    ($this->kirim)(['nama' => 'Batik'])
        ->assertCreated()
        ->assertHeaderMissing('Idempotency-Replayed');

    $catatan = KunciIdempotensi::sole();

    expect($catatan->pengguna_id)->toBe($this->andi->id)
        ->and($catatan->rute)->toBe('POST api/_uji/catat')
        ->and($catatan->kode_status)->toBe(201)
        // Yang disimpan adalah respons. Badan permintaan tidak pernah disimpan.
        ->and($catatan->badan_respons)->not->toContain('Batik');
});

it('menjawab pengulangan dengan respons yang sama tanpa mengulang efeknya', function () {
    $this->sebagai($this->andi);

    $pertama = ($this->kirim)(['nama' => 'Batik'])->assertCreated();
    $kedua = ($this->kirim)(['nama' => 'Batik'])
        ->assertCreated()
        ->assertHeader('Idempotency-Replayed', 'true');
    ($this->kirim)(['nama' => 'Batik'])->assertCreated();

    expect($kedua->getContent())->toBe($pertama->getContent())
        ->and(Kategori::count())->toBe(1);
});

it('menolak kunci yang sama untuk badan yang berbeda', function () {
    $this->sebagai($this->andi);

    ($this->kirim)(['nama' => 'Batik'])->assertCreated();
    ($this->kirim)(['nama' => 'Kriya'])
        ->assertUnprocessable()
        ->assertJsonPath('galat.kode', 'LP-11');

    expect(Kategori::pluck('nama')->all())->toBe(['Batik']);
});

it('memisahkan kunci menurut pengguna', function () {
    $this->sebagai($this->andi);
    ($this->kirim)(['nama' => 'Batik'])->assertCreated();

    // Orang lain yang kebetulan memakai teks kunci yang sama tidak menerima respons milik Andi.
    $this->sebagai(Pengguna::factory()->create());
    ($this->kirim)(['nama' => 'Kriya'])
        ->assertCreated()
        ->assertHeaderMissing('Idempotency-Replayed');

    expect(Kategori::count())->toBe(2)
        ->and(KunciIdempotensi::count())->toBe(2);
});

it('tidak meninggalkan efek maupun catatan bila permintaan gagal', function (
    string $pemicu,
    int $status,
) {
    $this->sebagai($this->andi);

    ($this->kirim)(['nama' => 'Batik', $pemicu => true])->assertStatus($status);

    // Kategori sempat ditulis sebelum galat, lalu ikut dibatalkan bersama catatan kuncinya.
    expect(Kategori::count())->toBe(0)
        ->and(KunciIdempotensi::count())->toBe(0);
})->with([
    'galat aturan bisnis' => ['gagal', 409],
    'galat tak terduga' => ['meledak', 500],
]);

it('membolehkan kunci dipakai lagi setelah permintaan yang gagal', function () {
    $this->sebagai($this->andi);

    ($this->kirim)(['nama' => 'Batik', 'gagal' => true])->assertConflict();
    // Diulang: tetap gagal, tetap tanpa jejak.
    ($this->kirim)(['nama' => 'Batik', 'gagal' => true])->assertConflict();
    expect(KunciIdempotensi::count())->toBe(0);

    // Kunci yang SAMA, kali ini dengan badan yang sudah dibetulkan: dijalankan seperti baru.
    ($this->kirim)(['nama' => 'Batik'])
        ->assertCreated()
        ->assertHeaderMissing('Idempotency-Replayed');

    expect(Kategori::count())->toBe(1);
});
