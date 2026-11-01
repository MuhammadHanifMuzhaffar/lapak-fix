<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\GalatDomain;
use App\Services\LayananPembayaran;
use App\Services\TandaTanganGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * POST /webhook/pembayaran: satu-satunya rute tulis yang publik. Pemanggilnya gerbang pembayaran.
 *

 * Tidak ada pintu "tandai lunas" bagi pembeli. Pesanan menjadi dibayar hanya lewat kabar
 * bertanda tangan dari gerbang, dan jumlahnya dicocokkan dengan tagihan di basis data.
 */
final class WebhookPembayaranController extends Controller
{
    public function __invoke(
        Request $request,
        TandaTanganGateway $tandaTangan,
        LayananPembayaran $pembayaran,
    ): JsonResponse {
        // Tanda tangan dihitung atas badan MENTAH, persis seperti yang diterima. Badan yang
        // sudah diurai lalu disusun ulang bisa berbeda satu spasi dan tanda tangannya gagal.
        $tanda = $request->header(TandaTanganGateway::HEADER);

        if (! $tandaTangan->sah($tanda, $request->getContent())) {
            // Satu jawaban untuk semua sebab: tanpa header, rahasia salah, badan diubah, atau basi.
            throw new GalatDomain('TANDA_TANGAN_SALAH', 'Tanda tangan webhook tidak sah.', 401);
        }

        // Baru setelah pengirimnya terbukti, isinya dibaca.
        $data = Validator::make($request->all(), [
            'id_peristiwa' => ['required', 'string', 'max:64'],
            'kode_pembayaran' => ['required', 'string', 'max:30'],
            'jumlah_rupiah' => ['required', 'integer', 'min:1'],
        ])->validate();

        $hasil = $pembayaran->terima(
            $data['kode_pembayaran'], $data['jumlah_rupiah'], $data['id_peristiwa'],
        );

        return $this->sukses(['hasil' => $hasil]);
    }
}
