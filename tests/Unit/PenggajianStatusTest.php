<?php

namespace Tests\Unit;

use App\Models\Penggajian;
use App\Models\PenggajianRincian;
use PHPUnit\Framework\TestCase;

class PenggajianStatusTest extends TestCase
{
    public function test_konstanta_status_penggajian(): void
    {
        $this->assertSame('Belum Dibayar', Penggajian::STATUS_BELUM_DIBAYAR);
        $this->assertSame('Sudah Dibayar', Penggajian::STATUS_SUDAH_DIBAYAR);
        $this->assertSame('Draft', Penggajian::STATUS_DRAFT);
        $this->assertSame('Disahkan', Penggajian::STATUS_DISAHKAN);
    }

    public function test_nilai_status_dibayar_legacy_dipertahankan(): void
    {
        $this->assertSame(['Sudah Dibayar', 'Dibayar'], Penggajian::nilaiStatusDibayar());
    }

    public function test_pemetaan_status_legacy_terbaca(): void
    {
        $this->assertSame(['Belum Dibayar', 'Diproses', 'Draft'], Penggajian::statusDraft());
        $this->assertSame(['Disahkan'], Penggajian::statusDisahkan());
        $this->assertSame(Penggajian::nilaiStatusDibayar(), Penggajian::statusSudahDibayar());
    }

    public function test_konstanta_tipe_rincian(): void
    {
        $this->assertSame('pembayaran', PenggajianRincian::TIPE_PEMBAYARAN);
        $this->assertSame('pengurangan', PenggajianRincian::TIPE_PENGURANGAN);
    }

    public function test_konstanta_kategori_rincian(): void
    {
        $this->assertSame('Komisi Project', PenggajianRincian::KOMISI_PROJECT);
        $this->assertSame('Insentif', PenggajianRincian::INSENTIF);
        $this->assertSame('Bonus', PenggajianRincian::BONUS);
        $this->assertSame('Kasbon', PenggajianRincian::KASBON);
        $this->assertSame('Mistake', PenggajianRincian::MISTAKE);
        $this->assertSame('Custom', PenggajianRincian::CUSTOM);

        $this->assertSame(
            ['Komisi Project', 'Insentif', 'Bonus', 'Custom'],
            PenggajianRincian::KATEGORI_WAJIB_KETERANGAN
        );
    }
}