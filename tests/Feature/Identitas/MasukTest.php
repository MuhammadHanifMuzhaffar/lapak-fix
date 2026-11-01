<?php

use App\Models\Pengguna;
use App\Models\Sesi;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->sri = Pengguna::factory()->create([
        'surel' => 'sri@lapak.test', 'kata_sandi' => 'BatikSolo2026',
    ]);

    $this->masuk = fn (array $timpa = []) => $this->postJson('/api/v1/masuk', array_merge([
        'surel' => 'sri@lapak.test', 'kata_sandi' => 'BatikSolo2026', 'nama_perangkat' => 'Laptop Sri',
    ], $timpa));
});

it('menerbitkan kunci sesi yang langsung dapat dipakai', function () {
    $respons = ($this->masuk)()
        ->assertCreated()
        ->assertJsonPath('data.pengguna.surel', 'sri@lapak.test')
        ->assertJsonPath('data.sesi.nama_perangkat', 'Laptop Sri')
        ->assertJsonPath('data.sesi.sedang_dipakai', true)
        ->assertJsonPath('data.sesi.segar', true);

    $kunci = $respons->json('data.kunci');
    expect($kunci)->toMatch('/^lpk_[0-9A-Z]{26}\.[0-9a-f]{64}$/');

    $this->denganKunci($kunci)->getJson('/api/v1/saya')
        ->assertOk()
        ->assertJsonPath('data.pengguna.id', $this->sri->id);
});

it('tidak menyimpan kunci utuh di basis data (LP-14)', function () {
    $kunci = ($this->masuk)()->json('data.kunci');
    $rahasia = explode('.', $kunci)[1];

    expect(json_encode(DB::table('sesi')->get()))->not->toContain($rahasia);
});

it('menjawab persis sama untuk surel tak terdaftar dan kata sandi salah', function () {
    $sandiSalah = ($this->masuk)(['kata_sandi' => 'BatikSolo2027'])->assertUnauthorized();
    $surelAsing = ($this->masuk)(['surel' => 'tidak-ada@lapak.test'])->assertUnauthorized();

    expect($sandiSalah->json())->toBe($surelAsing->json())
        ->and($sandiSalah->json('galat.kode'))->toBe('KREDENSIAL_SALAH')
        ->and(Sesi::count())->toBe(0);
});

it('menerima surel dengan huruf besar dan spasi di ujung', function () {
    ($this->masuk)(['surel' => '  Sri@Lapak.TEST '])->assertCreated();
});

it('menolak akun ditangguhkan tanpa membocorkan statusnya kepada penebak', function () {
    $this->sri->forceFill(['status' => 'ditangguhkan'])->save();

    ($this->masuk)()->assertForbidden()->assertJsonPath('galat.kode', 'AKUN_DITANGGUHKAN');
    ($this->masuk)(['kata_sandi' => 'tebakan-salah'])
        ->assertUnauthorized()
        ->assertJsonPath('galat.kode', 'KREDENSIAL_SALAH');

    expect(Sesi::count())->toBe(0);
});

it('membuat satu sesi per perangkat dengan kunci yang berbeda', function () {
    $laptop = ($this->masuk)(['nama_perangkat' => 'Laptop'])->json('data.kunci');
    $ponsel = ($this->masuk)(['nama_perangkat' => 'Ponsel'])->json('data.kunci');

    expect($laptop)->not->toBe($ponsel)
        ->and(Sesi::where('pengguna_id', $this->sri->id)->count())->toBe(2);
});

it('menghentikan tebakan kata sandi pada percobaan keenam dalam satu menit', function () {
    foreach (range(1, 5) as $ke) {
        ($this->masuk)(['kata_sandi' => "tebakan-{$ke}"])->assertUnauthorized();
    }

    ($this->masuk)()->assertTooManyRequests()->assertJsonPath('galat.kode', 'TERLALU_SERING');

    $this->travel(61)->seconds();
    ($this->masuk)()->assertCreated();
});
