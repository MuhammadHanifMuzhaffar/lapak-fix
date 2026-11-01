<?php 
  
use App\Domain\Pesanan\PenomorPesanan; 
  
it('membentuk nomor dari tanggal dan id pesanan (LP-9)', function () { 
    $nomor = PenomorPesanan::untuk(123, new DateTimeImmutable('2026-10-27 03:15:00 UTC')); 
  
    expect($nomor)->toBe('LPK-20261027-000123') 
        ->and($nomor)->toMatch(PenomorPesanan::POLA); 
}); 
  
it('memakai tanggal WIB, bukan tanggal UTC', function () { 
    // 23.30 UTC tanggal 27 adalah 06.30 WIB tanggal 28. 
    expect(PenomorPesanan::untuk(7, new DateTimeImmutable('2026-10-27 23:30:00 UTC'))) 
        ->toBe('LPK-20261028-000007'); 
}); 
  
it('tidak memotong id yang melewati enam angka', function () { 
    expect(PenomorPesanan::untuk(1_234_567, new DateTimeImmutable('2026-10-27 03:00:00 UTC'))) 
        ->toBe('LPK-20261027-1234567') 
        ->toMatch(PenomorPesanan::POLA); 
});