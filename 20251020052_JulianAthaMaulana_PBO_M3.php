<?php
/**
 * Tugas Pertemuan 3 - Pemrograman Berorientasi Objek (TI033015)
 * Class : LaporanKehilangan (kandidat class dari Tugas Pertemuan 2)
 * Kasus : Sistem Kehilangan dan Temuan Barang di Lingkungan Kampus
 */

// LANGKAH 1: Mendefinisikan class sebagai rancangan (blueprint)
class LaporanKehilangan
{
    // LANGKAH 2: Mendefinisikan property (data / state object)
    public string $idLaporan;
    public string $namaPelapor;
    public string $namaBarang;
    public string $lokasiTerakhir;
    public string $status = "dilaporkan"; // nilai awal bawaan

    // LANGKAH 3: Constructor, dijalankan otomatis saat object dibuat dengan "new"
    public function __construct(
        string $idLaporan,
        string $namaPelapor,
        string $namaBarang,
        string $lokasiTerakhir
    ) {
        // $this merujuk ke object yang sedang dibuat
        $this->idLaporan      = $idLaporan;
        $this->namaPelapor    = $namaPelapor;
        $this->namaBarang     = $namaBarang;
        $this->lokasiTerakhir = $lokasiTerakhir;
    }

    // LANGKAH 4: Method pertama, menampilkan data (membaca state)
    public function tampilkanInfo(): void
    {
        echo "ID Laporan     : $this->idLaporan\n";
        echo "Pelapor        : $this->namaPelapor\n";
        echo "Barang         : $this->namaBarang\n";
        echo "Lokasi Terakhir: $this->lokasiTerakhir\n";
        echo "Status         : $this->status\n";
    }

    // LANGKAH 5: Method kedua, mengubah status (mengubah state)
    public function ubahStatus(string $statusBaru): void
    {
        $this->status = $statusBaru;
        echo "Status laporan $this->idLaporan diubah menjadi '$this->status'.\n";
    }
}

// LANGKAH 6: Instantiation, membuat 2 object dengan data berbeda
$laporan1 = new LaporanKehilangan("LK-001", "Budi", "Dompet hitam", "Perpustakaan");
$laporan2 = new LaporanKehilangan("LK-002", "Sari", "Botol minum biru", "Kantin");

// LANGKAH 7: Memanggil method dan mengamati perubahan state
echo "=== Kondisi Awal ===\n";
echo "--- Object 1 ---\n";
$laporan1->tampilkanInfo();
echo "\n--- Object 2 ---\n";
$laporan2->tampilkanInfo();

echo "\n=== Setelah Status Object 1 Diubah ===\n";
$laporan1->ubahStatus("cocok");
$laporan1->tampilkanInfo();

echo "\n=== Object 2 Tidak Terpengaruh ===\n";
$laporan2->tampilkanInfo();
