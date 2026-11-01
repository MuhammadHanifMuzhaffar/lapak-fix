<?php

use App\Models\Alamat;
use App\Models\Pengguna;

function dataAlamat(array $timpa = []): array
{
    return array_merge([
        'label' => 'Rumah',
        'nama_penerima' => 'Andi Nugroho',
        'telepon' => '081200000008',
        'jalan' => 'Jl. Slamet Riyadi No. 12',
        'kota' => 'Surakarta',
        'kode_pos' => '57111',
    ], $timpa);
}

beforeEach(function () {
    $this->andi = Pengguna::factory()->pembeli()->create();
});

it('mendaftar alamat milik sendiri saja, alamat utama lebih dulu', function () {
    Alamat::factory()->cadangan()->for($this->andi, 'pengguna')
        ->create(['jalan' => 'Jl. Kantor 1']);
    Alamat::factory()->for($this->andi, 'pengguna')->create(['jalan' => 'Jl. Rumah 2']);
    Alamat::factory()->create(['jalan' => 'Jl. Orang Lain 3']);

    $this->sebagai($this->andi)->getJson('/api/v1/alamat')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.jalan', 'Jl. Rumah 2')
        ->assertJsonPath('data.0.utama', true)
        ->assertJsonPath('data.1.jalan', 'Jl. Kantor 1')
        ->assertJsonMissingPath('data.0.pengguna_id');
});

it('memotret alamat tanpa membawa pengenal dan label', function () {
    $alamat = Alamat::factory()->for($this->andi, 'pengguna')->create([
        'nama_penerima' => 'Andi Nugroho', 'telepon' => '081200000008',
        'jalan' => 'Jl. Slamet Riyadi No. 12', 'kota' => 'Surakarta', 'kode_pos' => '57111',
    ]);

    expect($alamat->potret())->toBe([
        'nama_penerima' => 'Andi Nugroho',
        'telepon' => '081200000008',
        'jalan' => 'Jl. Slamet Riyadi No. 12',
        'kota' => 'Surakarta',
        'kode_pos' => '57111',
    ]);
});

it('menjadikan alamat pertama sebagai alamat utama, dan alamat berikutnya bukan', function () {
    $this->sebagai($this->andi);

    $this->postJson('/api/v1/alamat', dataAlamat())
        ->assertCreated()
        ->assertJsonPath('data.utama', true);
    $this->postJson('/api/v1/alamat', dataAlamat(['label' => 'Kantor']))
        ->assertCreated()
        ->assertJsonPath('data.utama', false);

    expect(Alamat::query()->milik($this->andi)->where('utama', true)->count())->toBe(1);
});

it('mengabaikan pemilik dan tanda utama kiriman klien', function () {
    $orangLain = Pengguna::factory()->pembeli()->create();
    Alamat::factory()->for($this->andi, 'pengguna')->create();

    $this->sebagai($this->andi)
        ->postJson('/api/v1/alamat', dataAlamat(['pengguna_id' => $orangLain->id, 'utama' => true]))
        ->assertCreated()
        ->assertJsonPath('data.utama', false);

    expect(Alamat::query()->milik($orangLain)->count())->toBe(0);
});

it('membatasi jumlah alamat', function () {
    config(['lapak.transaksi.alamat_maksimum' => 2]);
    $this->sebagai($this->andi);

    $this->postJson('/api/v1/alamat', dataAlamat())->assertCreated();
    $this->postJson('/api/v1/alamat', dataAlamat(['label' => 'Kantor']))->assertCreated();
    $this->postJson('/api/v1/alamat', dataAlamat(['label' => 'Kos']))
        ->assertUnprocessable()
        ->assertJsonPath('galat.kode', 'ALAMAT_PENUH');
});

it('memindahkan tanda utama sehingga selalu tepat satu', function () {
    $rumah = Alamat::factory()->for($this->andi, 'pengguna')->create();
    $kantor = Alamat::factory()->cadangan()->for($this->andi, 'pengguna')->create();
    $this->sebagai($this->andi);

    $this->putJson("/api/v1/alamat/{$kantor->id}/utama")
        ->assertOk()
        ->assertJsonPath('data.id', $kantor->id)
        ->assertJsonPath('data.utama', true);
    $this->putJson("/api/v1/alamat/{$kantor->id}/utama")->assertOk();

    expect($rumah->refresh()->utama)->toBeFalse()
        ->and($kantor->refresh()->utama)->toBeTrue();
});

it('menjawab 404 untuk alamat milik orang lain dan tidak mengubahnya', function () {
    Alamat::factory()->for($this->andi, 'pengguna')->create();
    $milikLain = Alamat::factory()->cadangan()->create();
    $this->sebagai($this->andi);

    $this->putJson("/api/v1/alamat/{$milikLain->id}/utama")->assertNotFound();
    $this->deleteJson("/api/v1/alamat/{$milikLain->id}")->assertNotFound();
    $this->deleteJson('/api/v1/alamat/abc')->assertNotFound();

    expect($milikLain->refresh()->utama)->toBeFalse()
        ->and(Alamat::query()->milik($this->andi)->where('utama', true)->count())->toBe(1);
});
