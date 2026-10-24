<?php
declare(strict_types=1);

/**
 * Tugas Pertemuan 5 - Pemrograman Berorientasi Objek (TI033015)
 * Topik : Advanced OOP (inheritance, overriding, polymorphism, abstraction,
 *         interface, composition)
 * Kasus : Sistem Kehilangan dan Temuan Barang di Lingkungan Kampus
 */

// LANGKAH 1: Interface = kontrak kemampuan ("can-do")
interface BisaDicocokkan
{
    public function kunciPencocokan(): string;
}

// LANGKAH 2: Barang, kandidat class dari analisis. Mengimplementasikan interface.
class Barang implements BisaDicocokkan
{
    private string $nama;
    private string $ciri;

    public function __construct(string $nama, string $ciri)
    {
        if (trim($nama) === "") {
            throw new InvalidArgumentException("Nama barang tidak boleh kosong");
        }
        $this->nama = trim($nama);
        $this->ciri = trim($ciri);
    }

    public function getNama(): string
    {
        return $this->nama;
    }

    public function getCiri(): string
    {
        return $this->ciri;
    }

    public function kunciPencocokan(): string
    {
        return strtolower($this->nama);
    }
}

// LANGKAH 3: Abstract class = konsep dasar bersama untuk semua jenis laporan
abstract class Laporan implements BisaDicocokkan
{
    private const STATUS_VALID = ["dilaporkan", "cocok", "selesai"];

    private string $status = "dilaporkan";
    protected string $idLaporan;
    protected Barang $barang; // COMPOSITION: Laporan memiliki (has-a) Barang
    protected string $lokasi;

    public function __construct(string $idLaporan, Barang $barang, string $lokasi)
    {
        if (trim($idLaporan) === "" || trim($lokasi) === "") {
            throw new InvalidArgumentException("ID laporan dan lokasi tidak boleh kosong");
        }
        $this->idLaporan = trim($idLaporan);
        $this->barang = $barang;
        $this->lokasi = trim($lokasi);
    }

    // Wajib diwujudkan setiap subclass: tiap jenis laporan punya deskripsi sendiri
    abstract public function deskripsi(): string;

    // Method biasa yang boleh di-override subclass
    public function masaBerlaku(): int
    {
        return 30; // hari
    }

    // Method biasa yang dipakai bersama; memanggil method abstract/override
    public function ringkas(): string
    {
        return sprintf(
            "[%s] %s\n    ciri: %s | berlaku %d hari | status: %s",
            $this->idLaporan,
            $this->deskripsi(),
            $this->barang->getCiri(),
            $this->masaBerlaku(),
            $this->status
        );
    }

    public function getIdLaporan(): string
    {
        return $this->idLaporan;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getBarang(): Barang
    {
        return $this->barang;
    }

    // Memenuhi kontrak interface dengan mendelegasikan ke komponen Barang
    public function kunciPencocokan(): string
    {
        return $this->barang->kunciPencocokan();
    }

    public function ubahStatus(string $statusBaru): void
    {
        $urutLama = array_search($this->status, self::STATUS_VALID, true);
        $urutBaru = array_search($statusBaru, self::STATUS_VALID, true);

        if ($urutBaru === false || $urutBaru !== $urutLama + 1) {
            throw new LogicException(
                "Status '$this->status' tidak bisa menjadi '$statusBaru'"
            );
        }

        $this->status = $statusBaru;
    }
}

// LANGKAH 4: Subclass pertama (INHERITANCE: LaporanKehilangan adalah Laporan)
class LaporanKehilangan extends Laporan
{
    private string $namaPelapor;

    public function __construct(
        string $idLaporan,
        string $namaPelapor,
        Barang $barang,
        string $lokasiTerakhir
    ) {
        parent::__construct($idLaporan, $barang, $lokasiTerakhir);
        if (mb_strlen(trim($namaPelapor)) < 3) {
            throw new InvalidArgumentException("Nama pelapor minimal 3 karakter");
        }
        $this->namaPelapor = trim($namaPelapor);
    }

    public function deskripsi(): string
    {
        return "KEHILANGAN: $this->namaPelapor kehilangan "
            . "{$this->barang->getNama()}, terakhir di $this->lokasi";
    }
}

// LANGKAH 5: Subclass kedua dengan OVERRIDING masaBerlaku()
class LaporanTemuan extends Laporan
{
    private string $namaPenemu;
    private string $tempatSimpan;

    public function __construct(
        string $idLaporan,
        string $namaPenemu,
        Barang $barang,
        string $lokasiDitemukan,
        string $tempatSimpan
    ) {
        parent::__construct($idLaporan, $barang, $lokasiDitemukan);
        if (mb_strlen(trim($namaPenemu)) < 3) {
            throw new InvalidArgumentException("Nama penemu minimal 3 karakter");
        }
        $this->namaPenemu = trim($namaPenemu);
        $this->tempatSimpan = trim($tempatSimpan);
    }

    public function deskripsi(): string
    {
        return "TEMUAN: $this->namaPenemu menemukan {$this->barang->getNama()} "
            . "di $this->lokasi (simpan: $this->tempatSimpan)";
    }

    // OVERRIDING: barang temuan disimpan lebih lama daripada laporan biasa
    public function masaBerlaku(): int
    {
        return 90; // hari
    }
}

// LANGKAH 6: Klaim, tersusun dari dua laporan (COMPOSITION / has-a)
class Klaim
{
    private string $idKlaim;
    private LaporanKehilangan $kehilangan;
    private LaporanTemuan $temuan;
    private bool $terverifikasi = false;

    public function __construct(
        string $idKlaim,
        LaporanKehilangan $kehilangan,
        LaporanTemuan $temuan
    ) {
        $this->idKlaim = $idKlaim;
        $this->kehilangan = $kehilangan;
        $this->temuan = $temuan;
    }

    // Bergantung pada interface, bukan pada class konkret
    private function cocok(BisaDicocokkan $a, BisaDicocokkan $b): bool
    {
        return $a->kunciPencocokan() === $b->kunciPencocokan();
    }

    public function verifikasi(): bool
    {
        if (!$this->cocok($this->kehilangan, $this->temuan)) {
            echo "Klaim $this->idKlaim ditolak: barang pada kedua laporan tidak cocok.\n";
            return false;
        }

        $this->kehilangan->ubahStatus("cocok");
        $this->temuan->ubahStatus("cocok");
        $this->terverifikasi = true;
        echo "Klaim $this->idKlaim terverifikasi: laporan "
            . $this->kehilangan->getIdLaporan() . " dan "
            . $this->temuan->getIdLaporan() . " cocok.\n";
        return true;
    }

    public function serahkan(): void
    {
        if (!$this->terverifikasi) {
            throw new LogicException("Klaim $this->idKlaim belum terverifikasi");
        }

        $this->kehilangan->ubahStatus("selesai");
        $this->temuan->ubahStatus("selesai");
        echo "Klaim $this->idKlaim selesai: barang diserahkan kepada pemilik.\n";
    }
}

// LANGKAH 7: POLYMORPHISM, satu fungsi pemanggil untuk semua jenis Laporan
function cetakRingkasan(Laporan ...$daftar): void
{
    foreach ($daftar as $laporan) {
        echo $laporan->ringkas(), "\n"; // tanpa if/else berdasarkan nama class
    }
}

// Polymorphism lewat interface: Barang dan Laporan tidak satu parent
function tampilkanKunci(BisaDicocokkan ...$daftar): void
{
    foreach ($daftar as $item) {
        echo str_pad(get_class($item), 18) . "-> kunci: " . $item->kunciPencocokan() . "\n";
    }
}

// LANGKAH 8: Program utama
$dompet = new Barang("Dompet hitam", "kulit, ada kartu mahasiswa");
$botol = new Barang("Botol minum biru", "tutup putih");

$lk1 = new LaporanKehilangan("LK-001", "Budi", $dompet, "Perpustakaan");
$lt1 = new LaporanTemuan("LT-001", "Rina", $dompet, "Perpustakaan lantai 2", "Pos Satpam");
$lk2 = new LaporanKehilangan("LK-002", "Sari", $botol, "Kantin");

echo "=== 1. Polymorphism: satu fungsi, dua jenis laporan ===\n";
cetakRingkasan($lk1, $lt1, $lk2);

echo "\n=== 2. Interface BisaDicocokkan (Barang dan Laporan tidak satu parent) ===\n";
tampilkanKunci($dompet, $lk1, $lt1);

echo "\n=== 3. Composition: Klaim yang tidak cocok ===\n";
$klaimSalah = new Klaim("KL-002", $lk2, $lt1);
$klaimSalah->verifikasi();

echo "\n=== 4. Composition: Klaim yang cocok ===\n";
$klaim = new Klaim("KL-001", $lk1, $lt1);
if ($klaim->verifikasi()) {
    $klaim->serahkan();
}

echo "\n=== 5. Status akhir (polymorphism lagi) ===\n";
cetakRingkasan($lk1, $lt1, $lk2);

echo "\n=== 6. Abstract class tidak bisa dibuat langsung ===\n";
try {
    $x = new Laporan("X-001", $dompet, "Kantin");
} catch (Error $e) {
    echo "DITOLAK (" . get_class($e) . "): " . $e->getMessage() . "\n";
}
