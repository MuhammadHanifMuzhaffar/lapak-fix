<?php

declare(strict_types=1);

namespace App\Balapan;

/**
 * Satu skenario uji balapan: beberapa proses PHP melakukan hal yang sama pada saat yang sama,
 * lalu keadaan akhirnya diperiksa terhadap sebuah aturan yang tidak boleh dilanggar.
 *
 * Dijalankan dengan:  php artisan lapak:balapan <nama-skenario>
 * Nama skenario adalah nama kelasnya dengan tanda hubung: RebutanStok -> rebutan-stok.
 */
interface SkenarioBalapan
{
    public function keterangan(): string;

    /** Jumlah proses yang dilepas bersamaan. */
    public function proses(): int;

    /**
     * Menyiapkan data di basis data dan mengembalikan konteks untuk setiap proses anak.
     * Dipanggil satu kali, di proses induk, sebelum balapan dimulai.
     *
     * @return array<string, mixed> harus dapat diubah menjadi JSON
     */
    public function siapkan(): array;

    /**
     * Dipanggil di SETIAP proses anak, pada detik yang sama.
     *
     * @param  array<string, mixed>  $konteks
     * @return string hasil singkat, misalnya "berhasil" atau "409 LP-4"
     */
    public function jalankan(array $konteks, int $nomor): string;

    /**
     * Dipanggil satu kali di proses induk setelah semua anak selesai.
     *
     * @param  array<string, mixed>  $konteks
     * @param  list<string>  $hasil  satu hasil dari setiap proses anak
     * @return list<string> pelanggaran yang ditemukan; kosong berarti lulus
     */
    public function periksa(array $konteks, array $hasil): array;
}
