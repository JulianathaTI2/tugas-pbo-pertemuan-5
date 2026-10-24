<?php
/**
 * Tugas Pertemuan 4 - Pemrograman Berorientasi Objek (TI033015)
 * Topik : Encapsulation (refactoring class LaporanKehilangan dari Pertemuan 3)
 * Kasus : Sistem Kehilangan dan Temuan Barang di Lingkungan Kampus
 */

// LANGKAH 1: Class dengan state penting yang dilindungi (private)
class LaporanKehilangan
{
    private const STATUS_VALID = ["dilaporkan", "cocok", "selesai"];

    private string $idLaporan;
    private string $namaPelapor;
    private string $namaBarang;
    private string $lokasiTerakhir;
    private string $status = "dilaporkan";

    // LANGKAH 2: Constructor memvalidasi data sejak objek dibuat
    public function __construct(
        string $idLaporan,
        string $namaPelapor,
        string $namaBarang,
        string $lokasiTerakhir
    ) {
        $this->idLaporan      = self::wajibIsi($idLaporan, "ID laporan");
        $this->namaBarang     = self::wajibIsi($namaBarang, "Nama barang");
        $this->lokasiTerakhir = self::wajibIsi($lokasiTerakhir, "Lokasi terakhir");

        if (mb_strlen(trim($namaPelapor)) < 3) {
            throw new InvalidArgumentException("Nama pelapor minimal 3 karakter");
        }
        $this->namaPelapor = trim($namaPelapor);
    }

    // Helper internal: detail validasi disembunyikan dari pemanggil
    private static function wajibIsi(string $nilai, string $label): string
    {
        if (trim($nilai) === "") {
            throw new InvalidArgumentException("$label tidak boleh kosong");
        }
        return trim($nilai);
    }

    // LANGKAH 3: Getter hanya untuk data yang memang perlu dibaca dari luar
    public function getIdLaporan(): string
    {
        return $this->idLaporan;
    }

    public function getNamaPelapor(): string
    {
        return $this->namaPelapor;
    }

    public function getNamaBarang(): string
    {
        return $this->namaBarang;
    }

    public function getLokasiTerakhir(): string
    {
        return $this->lokasiTerakhir;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    // LANGKAH 4: Method aksi dengan validasi (pengganti setter bebas)
    public function ubahLokasi(string $lokasiBaru): void
    {
        $this->lokasiTerakhir = self::wajibIsi($lokasiBaru, "Lokasi baru");
        echo "Lokasi laporan $this->idLaporan diubah menjadi '$this->lokasiTerakhir'.\n";
    }

    public function ubahStatus(string $statusBaru): void
    {
        if (!in_array($statusBaru, self::STATUS_VALID, true)) {
            $pilihan = implode(", ", self::STATUS_VALID);
            throw new InvalidArgumentException(
                "Status '$statusBaru' tidak dikenal (pilihan: $pilihan)"
            );
        }

        $urutLama = array_search($this->status, self::STATUS_VALID, true);
        $urutBaru = array_search($statusBaru, self::STATUS_VALID, true);

        if ($urutBaru !== $urutLama + 1) {
            throw new LogicException(
                "Status harus berurutan: '$this->status' tidak bisa "
                . "langsung menjadi '$statusBaru'"
            );
        }

        $this->status = $statusBaru;
        echo "Status laporan $this->idLaporan diubah menjadi '$this->status'.\n";
    }

    public function tampilkanInfo(): void
    {
        echo "ID Laporan     : $this->idLaporan\n";
        echo "Pelapor        : $this->namaPelapor\n";
        echo "Barang         : $this->namaBarang\n";
        echo "Lokasi Terakhir: $this->lokasiTerakhir\n";
        echo "Status         : $this->status\n";
    }
}

// LANGKAH 5: Helper pengujian, menangkap penolakan dan mencetak pesannya
function uji(string $judul, callable $aksi): void
{
    echo "--- $judul ---\n";
    try {
        $aksi();
    } catch (Throwable $e) {
        echo "DITOLAK (" . get_class($e) . "): " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// LANGKAH 6: Tujuh skenario pengujian (3 normal, 4 error)
$laporan1 = null;

uji("Skenario 1 [normal]: membuat objek dengan data valid", function () use (&$laporan1) {
    $laporan1 = new LaporanKehilangan("LK-001", "Budi", "Dompet hitam", "Perpustakaan");
    $laporan1->tampilkanInfo();
});

uji("Skenario 2 [normal]: status berubah berurutan", function () use (&$laporan1) {
    $laporan1->ubahStatus("cocok");
    $laporan1->ubahStatus("selesai");
    echo "Status akhir via getter: " . $laporan1->getStatus() . "\n";
});

uji("Skenario 3 [normal]: ubah lokasi lewat method aksi", function () {
    $laporan = new LaporanKehilangan("LK-002", "Sari", "Botol minum biru", "Kantin");
    $laporan->ubahLokasi("Gedung A Lantai 2");
    echo "Lokasi via getter: " . $laporan->getLokasiTerakhir() . "\n";
});

uji("Skenario 4 [error]: akses langsung property private", function () {
    $laporan = new LaporanKehilangan("LK-003", "Rina", "Payung", "Aula");
    $laporan->status = "selesai"; // seharusnya ditolak
    echo "Status berubah tanpa aturan (TIDAK SEHARUSNYA TERJADI)\n";
});

uji("Skenario 5 [error]: status tidak dikenal", function () {
    $laporan = new LaporanKehilangan("LK-004", "Dani", "Kunci motor", "Parkiran");
    $laporan->ubahStatus("hilang");
});

uji("Skenario 6 [error]: status melompat tanpa melewati 'cocok'", function () {
    $laporan = new LaporanKehilangan("LK-005", "Maya", "Jaket abu", "Kelas B2");
    $laporan->ubahStatus("selesai");
});

uji("Skenario 7 [error]: nama pelapor kurang dari 3 karakter", function () {
    new LaporanKehilangan("LK-006", "Al", "Topi", "Lapangan");
});

uji("Skenario 8 [error]: lokasi baru kosong", function () {
    $laporan = new LaporanKehilangan("LK-007", "Tono", "Tas ransel", "Kantin");
    $laporan->ubahLokasi(" ");
});
