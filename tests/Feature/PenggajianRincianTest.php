<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use App\Models\Penggajian;
use App\Models\PenggajianKoreksi;
use App\Models\PenggajianRincian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenggajianRincianTest extends TestCase
{
    use RefreshDatabase;

    protected function makeKaryawan(array $overrides = []): Karyawan
    {
        return Karyawan::create(array_merge([
            'id_karyawan' => 'EMP-'.strtoupper(substr(uniqid(), -8)),
            'nama' => 'Karyawan Test',
            'email' => uniqid('test', true).'@example.dev',
            'departemen' => 'IT',
            'posisi' => '',
            'gaji_pokok' => 5000000,
            'tanggal_masuk' => '2025-01-01',
            'status' => 'Aktif',
        ], $overrides));
    }

    protected function makePenggajian(Karyawan $karyawan, array $overrides = []): Penggajian
    {
        return Penggajian::create(array_merge([
            'karyawan_id' => $karyawan->id,
            'periode' => '2026-07',
            'gaji_pokok' => (float) $karyawan->gaji_pokok,
            'total' => (float) $karyawan->gaji_pokok,
            'status' => Penggajian::STATUS_DRAFT,
            'tanggal_penggajian' => now()->toDateString(),
        ], $overrides));
    }

    public function test_banyak_rincian_per_kategori_dihitung_dari_seluruh_rincian(): void
    {
        $karyawan = $this->makeKaryawan(['gaji_pokok' => 5000000]);
        $penggajian = $this->makePenggajian($karyawan);

        $penggajian->rincian()->createMany([
            ['tipe' => PenggajianRincian::TIPE_PEMBAYARAN, 'kategori' => PenggajianRincian::KOMISI_PROJECT, 'nominal' => 1000000, 'keterangan' => 'Project A'],
            ['tipe' => PenggajianRincian::TIPE_PEMBAYARAN, 'kategori' => PenggajianRincian::KOMISI_PROJECT, 'nominal' => 500000, 'keterangan' => 'Project B'],
            ['tipe' => PenggajianRincian::TIPE_PEMBAYARAN, 'kategori' => PenggajianRincian::INSENTIF, 'nominal' => 200000, 'keterangan' => 'Produksi bulan ini'],
            ['tipe' => PenggajianRincian::TIPE_PEMBAYARAN, 'kategori' => PenggajianRincian::BONUS, 'nominal' => 150000, 'keterangan' => 'Bonus kinerja'],
            ['tipe' => PenggajianRincian::TIPE_PENGURANGAN, 'kategori' => PenggajianRincian::KASBON, 'nominal' => 500000],
            ['tipe' => PenggajianRincian::TIPE_PENGURANGAN, 'kategori' => PenggajianRincian::KASBON, 'nominal' => 200000],
            ['tipe' => PenggajianRincian::TIPE_PENGURANGAN, 'kategori' => PenggajianRincian::MISTAKE, 'nominal' => 100000],
            ['tipe' => PenggajianRincian::TIPE_PENGURANGAN, 'kategori' => PenggajianRincian::CUSTOM, 'nominal' => 300000, 'keterangan' => 'Potongan khusus'],
        ]);

        $this->assertSame(8, $penggajian->rincian()->count());
        $this->assertTrue($penggajian->punyaRincian());
        $this->assertEquals(1850000.0, $penggajian->totalPembayaranRincian());
        $this->assertEquals(1100000.0, $penggajian->totalPenguranganRincian());
        $this->assertEquals(5750000.0, $penggajian->gajiBersihHitung());
    }

    public function test_kategori_yang_wajib_keterangan(): void
    {
        $karyawan = $this->makeKaryawan();
        $penggajian = $this->makePenggajian($karyawan);

        foreach ([
            [PenggajianRincian::KOMISI_PROJECT, true],
            [PenggajianRincian::INSENTIF, true],
            [PenggajianRincian::BONUS, true],
            [PenggajianRincian::CUSTOM, true],
            [PenggajianRincian::KASBON, false],
            [PenggajianRincian::MISTAKE, false],
        ] as [$kategori, $wajib]) {
            $rincian = $penggajian->rincian()->create([
                'tipe' => $wajib ? PenggajianRincian::TIPE_PEMBAYARAN : PenggajianRincian::TIPE_PENGURANGAN,
                'kategori' => $kategori,
                'nominal' => 0,
            ]);

            $this->assertSame($wajib, $rincian->butuhKeterangan(), $kategori);
        }

        $this->assertTrue($penggajian->rincian()->first()->isPembayaran());
        $this->assertTrue(
            $penggajian->rincian()->where('kategori', PenggajianRincian::KASBON)->first()->isPengurangan()
        );
    }

    public function test_data_legacy_tanpa_rincian_membaca_gaji_bersih_historis(): void
    {
        $karyawan = $this->makeKaryawan(['gaji_pokok' => 9000000]);
        $penggajian = $this->makePenggajian($karyawan, [
            'status' => Penggajian::STATUS_BELUM_DIBAYAR,
            'gaji_bersih' => 9000000,
        ]);

        $this->assertFalse($penggajian->punyaRincian());
        $this->assertSame(0, $penggajian->rincian()->count());
        $this->assertEquals(9000000.0, $penggajian->gajiBersihHitung());
        $this->assertTrue($penggajian->isDraft());
        $this->assertFalse($penggajian->isDisahkan());
        $this->assertFalse($penggajian->isSudahDibayar());
        $this->assertTrue($penggajian->isBolehDibayar());
    }

    public function test_status_baru_draft_disahkan_dan_dibayar(): void
    {
        $karyawan = $this->makeKaryawan();
        $admin = User::factory()->create();

        $draft = $this->makePenggajian($karyawan, ['status' => Penggajian::STATUS_DRAFT]);
        $this->assertTrue($draft->isDraft());
        $this->assertFalse($draft->isBolehDibayar());

        $disahkan = $this->makePenggajian($karyawan, [
            'periode' => '2026-08',
            'status' => Penggajian::STATUS_DISAHKAN,
            'disahkan_at' => now(),
            'disahkan_oleh' => $admin->id,
        ]);
        $this->assertFalse($disahkan->isDraft());
        $this->assertTrue($disahkan->isDisahkan());
        $this->assertTrue($disahkan->isBolehDibayar());
        $this->assertNotNull($disahkan->disahkan_at);
        $this->assertSame($admin->id, $disahkan->disahkanOleh->id);

        $dibayar = $this->makePenggajian($karyawan, [
            'periode' => '2026-09',
            'status' => Penggajian::STATUS_SUDAH_DIBAYAR,
            'tanggal_pembayaran' => now()->toDateString(),
        ]);
        $this->assertTrue($dibayar->isSudahDibayar());
        $this->assertFalse($dibayar->isBolehDibayar());
        $this->assertFalse($dibayar->isDraft());
    }

    public function test_koreksi_menyimpan_json_dan_relasi_admin(): void
    {
        $admin = User::factory()->create();
        $karyawan = $this->makeKaryawan();
        $penggajian = $this->makePenggajian($karyawan, [
            'status' => Penggajian::STATUS_DISAHKAN,
            'disahkan_oleh' => $admin->id,
        ]);

        $koreksi = $penggajian->koreksi()->create([
            'admin_id' => $admin->id,
            'alasan' => 'Kesalahan input insentif',
            'data_sebelum' => ['insentif' => 200000],
            'data_sesudah' => ['insentif' => 250000],
        ]);

        $this->assertInstanceOf(PenggajianKoreksi::class, $koreksi);
        $this->assertSame(['insentif' => 200000], $koreksi->data_sebelum);
        $this->assertSame(['insentif' => 250000], $koreksi->data_sesudah);
        $this->assertSame('Kesalahan input insentif', $koreksi->alasan);
        $this->assertSame($admin->id, $koreksi->admin_id);
        $this->assertSame($admin->id, $koreksi->admin->id);
        $this->assertSame($penggajian->id, $koreksi->penggajian_id);
    }

    public function test_hapus_penggajian_draft_menghapus_rincian_secara_cascade(): void
    {
        $karyawan = $this->makeKaryawan();
        $penggajian = $this->makePenggajian($karyawan, ['status' => Penggajian::STATUS_DRAFT]);
        $penggajian->rincian()->create([
            'tipe' => PenggajianRincian::TIPE_PEMBAYARAN,
            'kategori' => PenggajianRincian::KOMISI_PROJECT,
            'nominal' => 100000,
            'keterangan' => 'Project X',
        ]);

        $penggajianId = $penggajian->id;

        $penggajian->delete();

        $this->assertDatabaseMissing('penggajian', ['id' => $penggajianId]);
        $this->assertDatabaseMissing('penggajian_rincian', ['penggajian_id' => $penggajianId]);
    }
}