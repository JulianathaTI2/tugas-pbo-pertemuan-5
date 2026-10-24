# Mini Application OOP: Sistem Kehilangan dan Temuan Barang di Kampus

Tugas Pertemuan 5 - Pemrograman Berorientasi Objek (TI033015)  
**Nama**: Julian Atha Maulana  
**NIM**: 20251020052  
**Program Studi**: S1 Teknik Informatika  
**Institusi**: Universitas Catur Insan Cendekia (UCIC)  

---

## 1. Masalah
Di lingkungan kampus, kasus kehilangan maupun penemuan barang sering terjadi. Petugas kampus membutuhkan sistem terpadu berbasis OOP murni (tanpa database/framework) untuk:
- Mencatat laporan barang hilang dari pemilik.
- Mencatat laporan barang temuan dari penemu.
- Mencocokkan data barang antara laporan kehilangan dan temuan secara otomatis.
- Memproses klaim penyerahan barang kembali kepada pemilik yang sah.

Tantangan utamanya adalah menghindari redundansi kode pada data laporan yang serupa, memisahkan tanggung jawab (*single responsibility*), serta menyatukan kemampuan pencocokan barang antar entitas yang berbeda hierarki secara *loose coupling*.

---

## 2. Desain & Kandidat Class
Sistem dimodelkan menggunakan 4 kandidat class inti dari analisis kebutuhan, 1 abstract class, dan 1 interface:

| Class / Interface | Jenis | Peran Utama |
| :--- | :--- | :--- |
| `BisaDicocokkan` | Interface | Mendefinisikan kontrak kemampuan pencocokan: `kunciPencocokan(): string`. |
| `Barang` | Class Konkret | Menyimpan data fisik barang (`nama`, `ciri`) dan mengimplementasikan `BisaDicocokkan`. |
| `Laporan` | Abstract Class | Fondasi dasar laporan (`idLaporan`, `barang`, `lokasi`, `status`), mengelola siklus status, method `ringkas()`, serta mewajibkan method abstract `deskripsi()`. |
| `LaporanKehilangan` | Subclass `Laporan` | Spesifik untuk laporan kehilangan; menambahkan identitas `namaPelapor`. |
| `LaporanTemuan` | Subclass `Laporan` | Spesifik untuk laporan temuan; menambahkan `namaPenemu`, `tempatSimpan`, serta meng-override masa simpan. |
| `Klaim` | Class Konkret | Mengelola proses verifikasi pencocokan antara laporan kehilangan dan temuan, serta penyerahan barang. |

---

## 3. Alasan Pilihan Relasi

1. **Inheritance ("is-a")**:
   - `LaporanKehilangan` **is-a** `Laporan` dan `LaporanTemuan` **is-a** `Laporan`.
   - **Alasan**: Keduanya berbagi struktur data yang sama (`idLaporan`, objek `Barang`, `lokasi`, `status`) serta aturan transisi status berurutan (`dilaporkan -> cocok -> selesai`). Pewarisan menghindari duplikasi logika dan menjaga enkapsulasi.

2. **Interface ("can-do")**:
   - `Barang` dan `Laporan` sama-sama mengimplementasikan `BisaDicocokkan`.
   - **Alasan**: `Barang` dan `Laporan` bukan satu keluarga hierarki (Barang bukan Laporan), namun sama-sama memiliki kemampuan menghasilkan kunci pencocokan teks (`kunciPencocokan()`). Class `Klaim` cukup bergantung pada kontrak interface ini, bukan class konkret.

3. **Composition ("has-a")**:
   - `Laporan` **has-a** `Barang`: Setiap laporan memiliki satu komponen objek barang yang dilaporkan.
   - `Klaim` **has-a** `LaporanKehilangan` dan `LaporanTemuan`: Objek klaim tidak mewarisi laporan, melainkan mengagregasikan kedua laporan untuk keperluan verifikasi dan serah terima.

---

## 4. Implementasi Konsep Advanced OOP

- **Abstraction**: `abstract class Laporan` dengan `abstract public function deskripsi(): string;`. Mencegah pembuatan objek laporan mentah tanpa tipe yang jelas.
- **Overriding**: Method `masaBerlaku()` pada `Laporan` bernilai default 30 hari, di-override pada `LaporanTemuan` menjadi 90 hari karena barang temuan perlu disimpan lebih lama di pos kampus.
- **Polymorphism**:
  - **Class Polymorphism**: Fungsi `cetakRingkasan(Laporan ...$daftar)` dapat mencetak ringkasan berbagai objek turunan `Laporan` tanpa perlu percabangan (`if/else`).
  - **Interface Polymorphism**: Fungsi `tampilkanKunci(BisaDicocokkan ...$daftar)` dapat mengekstrak kunci pencocokan secara seragam dari objek `Barang` maupun `Laporan`.

---

## 5. Cara Menjalankan Program
Pastikan PHP versi 8.0 atau yang lebih baru telah terinstal:
```bash
php 20251020052_JulianAthaMaulana_PBO_M5.php
```

---

## 6. Hasil Output Program
```text
=== 1. Polymorphism: satu fungsi, dua jenis laporan ===
[LK-001] KEHILANGAN: Budi kehilangan Dompet hitam, terakhir di Perpustakaan
    ciri: kulit, ada kartu mahasiswa | berlaku 30 hari | status: dilaporkan
[LT-001] TEMUAN: Rina menemukan Dompet hitam di Perpustakaan lantai 2 (simpan: Pos Satpam)
    ciri: kulit, ada kartu mahasiswa | berlaku 90 hari | status: dilaporkan
[LK-002] KEHILANGAN: Sari kehilangan Botol minum biru, terakhir di Kantin
    ciri: tutup putih | berlaku 30 hari | status: dilaporkan

=== 2. Interface BisaDicocokkan (Barang dan Laporan tidak satu parent) ===
Barang            -> kunci: dompet hitam
LaporanKehilangan -> kunci: dompet hitam
LaporanTemuan     -> kunci: dompet hitam

=== 3. Composition: Klaim yang tidak cocok ===
Klaim KL-002 ditolak: barang pada kedua laporan tidak cocok.

=== 4. Composition: Klaim yang cocok ===
Klaim KL-001 terverifikasi: laporan LK-001 dan LT-001 cocok.
Klaim KL-001 selesai: barang diserahkan kepada pemilik.

=== 5. Status akhir (polymorphism lagi) ===
[LK-001] KEHILANGAN: Budi kehilangan Dompet hitam, terakhir di Perpustakaan
    ciri: kulit, ada kartu mahasiswa | berlaku 30 hari | status: selesai
[LT-001] TEMUAN: Rina menemukan Dompet hitam di Perpustakaan lantai 2 (simpan: Pos Satpam)
    ciri: kulit, ada kartu mahasiswa | berlaku 90 hari | status: selesai
[LK-002] KEHILANGAN: Sari kehilangan Botol minum biru, terakhir di Kantin
    ciri: tutup putih | berlaku 30 hari | status: dilaporkan

=== 6. Abstract class tidak bisa dibuat langsung ===
DITOLAK (Error): Cannot instantiate abstract class Laporan
```








<img width="1920" height="1080" alt="Screenshot 2026-10-24 022145" src="https://github.com/user-attachments/assets/b75b9335-10af-4768-8c91-01892ee3387a" />
