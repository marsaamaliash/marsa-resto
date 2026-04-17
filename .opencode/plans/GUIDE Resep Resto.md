# Panduan Penggunaan Modul Resep (Recipe & BOM Management)

## Daftar Isi

1. [Ikhtisar](#1-ikhtisar)
2. [Navigasi & Akses](#2-navigasi--akses)
3. [Daftar Resep (Recipe Table)](#3-daftar-resep-recipe-table)
4. [Membuat Resep Baru](#4-membuat-resep-baru)
5. [Detail Resep (Recipe Show)](#5-detail-resep-recipe-show)
6. [Versi Resep](#6-versi-resep)
7. [Bahan / Bill of Materials (BOM)](#7-bahan--bill-of-materials-bom)
8. [Output Resep](#8-output-resep)
9. [Analisis Cost](#9-analisis-cost)
10. [Menu Terkait](#10-menu-terkait)
11. [Konversi Satuan](#11-konversi-satuan)
12. [Studi Kasus](#12-studi-kasus)
13. [Alur Approval Versi](#13-alur-approval-versi)
14. [Izin (Permissions)](#14-izin-permissions)

---

## 1. Ikhtisar

Modul Resep memungkinkan Anda menyusun komposisi bahan (Bill of Materials) untuk:

- **Preparation** — Barang setengah jadi (mis: bumbu base, saus) yang hasilnya masuk ke stok.
- **Menu** — Menu final yang dijual ke pelanggan.
- **Additional/Bundling** — Menu paket yang terdiri dari beberapa item/preparation.

Modul ini mendukung:

- **Versioning** — Setiap resep bisa punya banyak versi, hanya satu yang aktif.
- **Nested BOM** — Bahan bisa merujuk ke resep lain (sub-resep).
- **Approval flow** — Versi resep mengikuti alur Draft → Submitted → Approved/Rejected.
- **Perhitungan cost** — Material cost dihitung otomatis berdasarkan BOM, termasuk nested BOM.

---

## 2. Navigasi & Akses

Menu Resep berada di sidebar Resto:

| URL                                  | Halaman              |
| ------------------------------------ | -------------------- |
| `/dashboard/resto/resep`             | Dashboard Resep      |
| `/dashboard/resto/resep/recipe`      | Daftar Resep (tabel) |
| `/dashboard/resto/resep/recipe/{id}` | Detail Resep         |
| `/dashboard/resto/konversi-satuan`   | Konversi Satuan      |
| `/dashboard/resto/repack`            | Repack Stok          |

---

## 3. Daftar Resep (Recipe Table)

Halaman `/dashboard/resto/resep/recipe` menampilkan tabel semua resep.

### 3.1 Pencarian & Filter

- **Search** — Cari berdasarkan kode atau nama resep.
- **Filter Tipe** — Pilih `Preparation`, `Menu`, atau `Additional/Bundling`.
- **Filter Status** — Pilih `Aktif` atau `Nonaktif`.

### 3.2 Pengurutan

Klik header kolom untuk mengurutkan berdasarkan: ID, Kode Resep, Nama Resep, Tipe, Status Aktif, atau Tanggal Dibuat.

### 3.3 Export

- **Export Filtered** — Mengekspor semua data yang sesuai filter ke file `.xlsx`.
- **Export Selected** — Pilih checkbox pada baris tertentu, lalu ekspor data terpilih ke `.xlsx`.

### 3.4 Aksi per Baris

| Tombol        | Fungsi                    | Izin Diperlukan |
| ------------- | ------------------------- | --------------- |
| Lihat (eye)   | Buka halaman detail resep | —               |
| Edit (pencil) | Buka overlay edit resep   | `RECIPE_UPDATE` |
| Hapus (trash) | Soft-delete resep         | `RECIPE_DELETE` |

### 3.5 Tambah Resep

Klik tombol **"+ Tambah Resep"** (hanya muncul jika punya izin `RECIPE_CREATE`).

---

## 4. Membuat Resep Baru

Klik **"+ Tambah Resep"** di halaman daftar, lalu isi form:

| Field              | Wajib | Keterangan                                                     |
| ------------------ | ----- | -------------------------------------------------------------- |
| **Kode Resep**     | Tidak | Akan auto-generate format `RCP-YYYYMMDD-XXXX` jika dikosongkan |
| **Nama Resep**     | Ya    | Nama resep, maks 255 karakter                                  |
| **Tipe Resep**     | Ya    | Pilih: `preparation`, `menu`, atau `additional`                |
| **Item Output**    | Ya    | Pilih item dari Master Inventory yang menjadi hasil resep ini  |
| **Satuan Default** | Ya    | Satuan dasar resep (mis: gram, porsi)                          |
| **Issue Method**   | Ya    | Cara pengeluaran bahan: `batch_actual`, `manual`, atau `fifo`  |
| **Yield Tracking** | Ya    | Mode pelacakan hasil: `strict` atau `flexible`                 |
| **Aktif**          | Ya    | Centang jika resep langsung aktif                              |
| **Catatan**        | Tidak | Keterangan tambahan                                            |

### Tipe Resep

- **Preparation** — Barang setengah jadi. Hasilnya masuk ke stok dan bisa dipakai sebagai bahan di resep lain (Nested BOM). Contoh: Bumbu Base Nasi Goreng.
- **Menu** — Menu final yang dijual ke pelanggan. Contoh: Nasi Goreng Spesial.
- **Additional/Bundling** — Menu paket yang menggabungkan beberapa item/preparation. Contoh: Paket Nasi Goreng + Es Teh.

### Issue Method

- **Batch Actual** — Bahan dikeluarkan sesuai jumlah aktual yang tercatat saat produksi.
- **Manual** — Bahan dikeluarkan secara manual oleh operator.
- **FIFO** — Bahan dikeluarkan berdasarkan persediaan yang masuk lebih dulu (First In First Out).

---

## 5. Detail Resep (Recipe Show)

Dari daftar resep, klik ikon mata (eye) untuk membuka halaman detail di `/dashboard/resto/resep/recipe/{id}`.

### 5.1 Header

Menampilkan:

- Nama resep, kode resep, tipe (badge warna), dan status aktif.
- Tombol **Aktifkan/Nonaktifkan** — Mengubah status aktif resep.
- Tombol **"← Kembali"** — Kembali ke daftar resep.
- Tombol **Edit** (overlay) — Mengubah informasi dasar resep.

### 5.2 Info Cards

Empat kartu informasi singkat:

1. **Item Output** — Item yang dihasilkan resep ini.
2. **Satuan Default** — Satuan dasar resep.
3. **Issue Method** — Mode pengeluaran bahan.
4. **Yield Tracking** — Mode pelacakan hasil.

Jika resep memiliki catatan, muncul kotak catatan tambahan.

### 5.3 Tab Navigasi

Terdapat 5 tab:

| Tab             | Keterangan                                 | Ketersediaan                   |
| --------------- | ------------------------------------------ | ------------------------------ |
| **Versi Resep** | Daftar semua versi resep                   | Selalu tersedia                |
| **Bahan (BOM)** | Daftar komponen/bahan untuk versi terpilih | Hanya jika versi sudah dipilih |
| **Output**      | Daftar output untuk versi terpilih         | Hanya jika versi sudah dipilih |
| **Cost**        | Analisis biaya untuk versi terpilih        | Hanya jika versi sudah dipilih |
| **Menu**        | Daftar menu yang terhubung ke resep ini    | Selalu tersedia                |

---

## 6. Versi Resep

### 6.1 Versi Selector

Di bagian atas area tab, terdapat tombol-tombol versi (V1, V2, dst.). Setiap tombol menampilkan:

- Nomor versi
- Badge aktif (titik hijau) jika `is_active`
- Badge status: `(draft)`, `(submitted)`, `(approved)`

Klik tombol versi untuk memilih versi aktif. Informasi versi terpilih muncul di panel biru muda.

### 6.2 Info Versi Terpilih

Panel info menampilkan:

- **Versi** — Nomor versi
- **Batch Size** — Jumlah per batch + satuan
- **Expected Output** — Jumlah output yang diharapkan + satuan
- **Yield %** — Persentase yield yang diharapkan
- **Approval** — Status approval (Draft/Submitted/Approved/Rejected)

### 6.3 Membuat Versi Baru

1. Klik tombol **"+ Tambah Versi"** (hanya muncul jika punya izin `RECIPE_VERSION_CREATE`).
2. Isi form:
    - **Nomor Versi** — Angka, auto-fill dari versi terakhir + 1.
    - **Keterangan Versi** — Misal: "V1 - Standar", "V2 - Promo Ramadan".
3. Klik **"Simpan Versi"**.

Versi baru otomatis berstatus **Draft**.

> **Catatan:** Saat dibuat, `batch_size_qty` dan `expected_output_qty` diisi default (1) dengan satuan mengikuti satuan default resep. Nilai ini perlu disesuaikan melalui edit jika diperlukan.

### 6.4 Action per Versi

Setiap baris versi memiliki tombol aksi (hanya muncul sesuai izin dan status):

| Aksi         | Status Valid    | Izin Diperlukan         | Keterangan                                                                                             |
| ------------ | --------------- | ----------------------- | ------------------------------------------------------------------------------------------------------ |
| **Aktifkan** | Draft, Approved | `RECIPE_VERSION_UPDATE` | Menandai versi ini sebagai versi aktif; versi lain otomatis dinonaktifkan                              |
| **Submit**   | Draft           | `RECIPE_VERSION_UPDATE` | Mengirim versi untuk approval, status berubah ke Submitted                                             |
| **Approve**  | Submitted       | `RECIPE_VERSION_UPDATE` | Menyetujui versi; status berubah ke Approved; versi otomatis diaktifkan; cost snapshot otomatis dibuat |
| **Reject**   | Submitted       | `RECIPE_VERSION_UPDATE` | Menolak versi; status berubah ke Rejected                                                              |
| **Hapus**    | Draft           | `RECIPE_VERSION_DELETE` | Soft-delete versi (hanya jika masih Draft)                                                             |

### 6.5 Alur Approval Lengkap

```
Draft → Submitted → Approved
                     ↑
                   (versi otomatis aktif, cost snapshot dibuat)

Draft → Submitted → Rejected
                     ↑
                (bisa buat versi baru untuk mengganti)
```

Lihat juga [Bagian 13 — Alur Approval Versi](#13-alur-approval-versi).

---

## 7. Bahan / Bill of Materials (BOM)

Tab BOM menampilkan daftar komponen/bahan untuk versi resep yang dipilih.

> **Penting:** Perubahan BOM (tambah/edit/hapus) hanya bisa dilakukan pada versi dengan status **Draft**.

### 7.1 Menambah Komponen Bahan

1. Pilih versi berstatus **Draft**.
2. Klik tombol **"+ Tambah Bahan"** (hanya muncul jika versi berstatus Draft dan punya izin `RECIPE_VERSION_CREATE`).
3. Isi form ComponentForm:
    - **Tipe Komponen** — Pilih `Item` (bahan baku dari inventory) atau `Resep` (sub-resep/Nested BOM).
    - Jika **Item**: Pilih item dari dropdown Master Inventory.
    - Jika **Resep**: Pilih resep dari dropdown resep yang aktif. Sistem otomatis mencegah referensi sirkular.
    - **Stage** — Tahap penggunaan bahan (default: `main`).
    - **Qty Standard** — Jumlah kebutuhan bahan per batch.
    - **Satuan (UOM)** — Pilih satuan dari daftar satuan.
    - **Wastage %** — Persentase pemborosan bahan (default: 0).
    - **Opsional** — Centang jika bahan ini bersifat opsional.
    - **Modifier Driven** — Centang jika bahan ini ditentukan oleh modifier/pilihan pelanggan.
    - **Catatan** — Keterangan tambahan.
4. Klik **"Simpan"**.

### 7.2 Mengedit Komponen Bahan

1. Klik ikon edit (pensil) pada baris komponen yang ingin diubah (hanya untuk versi Draft).
2. Ubah data pada form overlay.
3. Klik **"Simpan"**.

### 7.3 Menghapus Komponen Bahan

1. Klik ikon hapus (sampah) pada baris komponen (hanya versi Draft).

### 7.4 Keterangan Kolom Tabel BOM

| Kolom        | Keterangan                                   |
| ------------ | -------------------------------------------- |
| **Tipe**     | `Item` (bahan baku) atau `Resep` (sub-resep) |
| **Nama**     | Nama item atau nama resep yang dirujuk       |
| **Qty**      | Jumlah standar per batch                     |
| **Satuan**   | Satuan yang digunakan                        |
| **Wastage**  | Persentase pemborosan                        |
| **Opsional** | Ya/Tidak                                     |
| **Aksi**     | Edit/Hapus (hanya untuk versi Draft)         |

### 7.5 Nested BOM

Jika tipe komponen adalah **Resep**, artinya resep ini menggunakan hasil resep lain sebagai bahan. Sistem:

- Mencegah referensi sirkular (resep tidak bisa merujuk ke dirinya sendiri atau membentuk lingkaran).
- Secara otomatis menghitung material cost secara rekursif hingga kedalaman 10 level.

---

## 8. Output Resep

Tab Output menampilkan daftar hasil produksi untuk versi resep yang dipilih.

> **Penting:** Perubahan Output (tambah/edit/hapus) hanya bisa dilakukan pada versi dengan status **Draft**.

### 8.1 Tipe Output

| Tipe           | Keterangan                                                                    |
| -------------- | ----------------------------------------------------------------------------- |
| **Main**       | Output utama (untuk resep menu/preparation, ini adalah hasil yang diharapkan) |
| **By Product** | Hasil sampingan yang ikut dihasilkan                                          |
| **Co Product** | Hasil bersama yang memiliki nilai jual sendiri                                |
| **Waste**      | Limbah yang dihasilkan dari proses produksi                                   |

### 8.2 Menambah Output

1. Pilih versi berstatus **Draft**.
2. Klik tombol **"+ Tambah Output"**.
3. Isi form OutputForm:
    - **Tipe Output** — Pilih: `main`, `by_product`, `co_product`, atau `waste`.
    - **Item Output** — Pilih item dari Master Inventory.
    - **Planned Qty** — Jumlah yang direncanakan per batch.
    - **Satuan (UOM)** — Satuan output.
    - **Cost Allocation %** — Persentase alokasi biaya ke output ini (default: 100 untuk main).
    - **Inventory Item** — Centang jika output ini masuk ke stok inventori.
    - **Catatan** — Keterangan tambahan.
4. Klik **"Simpan"**.

### 8.3 Mengedit & Menghapus Output

Sama seperti BOM — klik ikon edit atau hapus pada baris output. Hanya untuk versi **Draft**.

### 8.4 Keterangan Kolom Tabel Output

| Kolom         | Keterangan                           |
| ------------- | ------------------------------------ |
| **Tipe**      | Main, By Product, Co Product, Waste  |
| **Nama Item** | Nama item output                     |
| **Qty**       | Jumlah yang direncanakan             |
| **Satuan**    | Satuan yang digunakan                |
| **Cost %**    | Persentase alokasi biaya             |
| **Inventori** | Ya/Tidak (apakah masuk stok)         |
| **Aksi**      | Edit/Hapus (hanya untuk versi Draft) |

---

## 9. Analisis Cost

Tab Cost menampilkan perhitungan biaya material untuk versi resep yang dipilih.

### 9.1 Ringkasan Cost

Tiga kartu menampilkan:

1. **Material Cost (estimasi)** — Total biaya bahan dihitung dari BOM (termasuk nested BOM dan wastage).
2. **Last Snapshot Date** — Tanggal cost snapshot terakhir.
3. **Cost Per Output Unit** — Biaya per unit output (dihitung dari total material cost ÷ planned output qty).

### 9.2 Menghitung Ulang Cost Snapshot

1. Klik tombol **"Hitung Ulang Cost Snapshot"**.
2. Sistem akan menghitung ulang material cost dari BOM terbaru dan menyimpan snapshot baru.

> Cost snapshot juga otomatis dibuat saat versi di-approve.

### 9.3 Nested BOM Warning

Jika resep memiliki komponen bertipe "Sub-Resep" (Nested BOM), muncul peringatan kuning yang menjelaskan bahwa cost dihitung secara rekursif dari seluruh rantai BOM.

### 9.4 Cost History

Jika terdapat lebih dari 1 cost snapshot, tabel riwayat menampilkan:

- **Date** — Tanggal snapshot.
- **Material Cost** — Total biaya bahan.
- **Batch Cost** — Total biaya per batch.
- **Cost/Unit** — Biaya per unit output.
- **Basis** — Metode perhitungan (saat ini selalu `standard`).

### 9.5 Rumus Perhitungan Material Cost

```
Material Cost per komponen =
    qty_standard × (1 + wastage_pct / 100) × unit_cost

Material Cost total =
    Σ (material cost per komponen Item)
    + Σ (material cost per komponen Resep yang di-resolve secara rekursif)

Cost Per Output Unit =
    Material Cost total ÷ expected_output_qty
```

---

## 10. Menu Terkait

Tab Menu menampilkan daftar menu POS yang terhubung (link) ke resep ini.

### 10.1 Melihat Menu Terkait

Tabel menampilkan:

- **Nama Menu**
- **Harga** — Harga menu di POS
- **Kategori** — Kategori menu
- **Aktif** — Status aktif menu
- **Aksi** — Tombol **Unlink**

### 10.2 Unlink Menu dari Resep

Klik tombol **"Unlink"** pada baris menu. Resep akan dilepaskan dari menu tersebut (field `recipe_id` di-set null).

> **Catatan:** Saat ini belum tersedia UI untuk menghubungkan (link) resep ke menu dari halaman detail resep. Fitur link dilakukan melalui pengaturan langsung di data menu (database).

---

## 11. Konversi Satuan

Halaman `/dashboard/resto/konversi-satuan` memungkinkan pengaturan faktor konversi antar satuan untuk setiap item.

### 11.1 Menambah Konversi Satuan

1. Klik **"+ Tambah Konversi"**.
2. Isi form:
    - **Item** — Pilih item dari Master Inventory.
    - **Dari Satuan** — Satuan asal (mis: kg).
    - **Ke Satuan** — Satuan tujuan (mis: gram).
    - **Faktor Konversi** — Nilai pengali (mis: 1000, karena 1 kg = 1000 gram).
3. Klik **"Simpan"**.

### 11.2 Kegunaan

Konversi satuan digunakan ketika:

- Resep menggunakan satuan berbeda dari satuan stok (mis: resep pakai gram, stok pakai kg).
- Perlu menghitung cost yang akurat berdasarkan satuan pembelian.

---

## 12. Studi Kasus

### Kasus 1: Preparation (Bumbu Base)

Membuat resep untuk bumbu base Nasi Goreng yang hasilnya menjadi barang setengah jadi:

1. **Buat Resep Baru:**
    - Nama: "Base Nasi Goreng"
    - Tipe: `Preparation`
    - Item Output: "Base Nasi Goreng" (item di Master Inventory)
    - Satuan Default: gram

2. **Buat Versi (V1):**
    - Batch Size: 500 gram
    - Expected Output: 500 gram
    - Satuan: gram

3. **Tambah Bahan (BOM):**
   | Bahan | Tipe | Qty | Satuan |
   |-------|------|-----|--------|
   | Bawang Merah | Item | 500 | gram |
   | Garam | Item | 50 | gram |

4. **Tambah Output:**
   | Tipe | Item | Qty | Satuan | Cost % | Inventory |
   |------|------|-----|--------|--------|-----------|
   | Main | Base Nasi Goreng | 500 | gram | 100 | Ya |

5. **Submit & Approve** versi untuk mengaktifkan.

### Kasus 2: Menu Additional (Bundling)

Membuat resep untuk Nasi Goreng Rendang yang menggunakan Base Nasi Goreng dari Kasus 1:

1. **Buat Resep Baru:**
    - Nama: "Nasi Goreng Rendang"
    - Tipe: `Additional`
    - Item Output: "Nasi Goreng Rendang" (item di Master Inventory)
    - Satuan Default: porsi

2. **Buat Versi (V1):**
    - Batch Size: 1 porsi
    - Expected Output: 1 porsi

3. **Tambah Bahan (BOM):**
   | Bahan | Tipe | Qty | Satuan |
   |-------|------|-----|--------|
   | Nasi Putih | Item | 1 | porsi |
   | Base Nasi Goreng | **Resep** | 20 | gram |
   | Rendang | **Resep** | 1 | pcs |

4. **Tambah Output:**
   | Tipe | Item | Qty | Satuan |
   |------|------|-----|--------|
   | Main | Nasi Goreng Rendang | 1 | porsi |

5. **Submit & Approve**, lalu klik **"Hitung Ulang Cost Snapshot"** untuk melihat total material cost yang sudah termasuk cost dari sub-resep (nested BOM).

---

## 13. Alur Approval Versi

```
┌──────────┐     Submit      ┌───────────┐     Approve     ┌──────────┐
│  Draft   │ ──────────────► │ Submitted │ ──────────────► │ Approved │
└──────────┘                 └───────────┘                  └──────────┘
                                  │                              │
                                  │ Reject                       │
                                  ▼                              ▼
                             ┌──────────┐              Versi otomatis
                             │ Rejected │              aktif + cost
                             └──────────┘              snapshot dibuat
```

### Aturan Penting

- Hanya versi berstatus **Draft** yang bisa diedit (ubah BOM, output, detail versi).
- Hanya versi berstatus **Draft** yang bisa dihapus.
- Hanya versi berstatus **Draft** yang bisa di-submit.
- Hanya versi berstatus **Submitted** yang bisa di-approve atau reject.
- Saat versi di-approve, sistem otomatis:
    1. Mengaktifkan versi tersebut (menonaktifkan versi lain).
    2. Mengaktifkan resep induknya.
    3. Membuat cost snapshot baru.

---

## 14. Izin (Permissions)

| Kode Izin               | Keterangan                                        |
| ----------------------- | ------------------------------------------------- |
| `RECIPE_CREATE`         | Membuat resep baru                                |
| `RECIPE_UPDATE`         | Mengedit resep, mengaktifkan/menonaktifkan        |
| `RECIPE_DELETE`         | Menghapus (soft-delete) resep                     |
| `RECIPE_VERSION_CREATE` | Membuat versi baru, menambah komponen/output      |
| `RECIPE_VERSION_UPDATE` | Mengubah versi, submit, approve, reject, aktifkan |
| `RECIPE_VERSION_DELETE` | Menghapus versi (hanya Draft)                     |
| `MASTER_SATUAN_CREATE`  | Membuat konversi satuan                           |
| `MASTER_SATUAN_UPDATE`  | Mengedit konversi satuan                          |
| `MASTER_SATUAN_DELETE`  | Menghapus konversi satuan                         |

---

## Referensi Teknis

### Struktur Database Utama

| Tabel                       | Keterangan                  |
| --------------------------- | --------------------------- |
| `rec_recipes`               | Data master resep           |
| `rec_recipe_versions`       | Versi resep (approval flow) |
| `rec_recipe_components`     | Bahan/komponen BOM          |
| `rec_recipe_outputs`        | Output resep                |
| `rec_recipe_cost_snapshots` | Snapshot perhitungan biaya  |
| `uom_conversions`           | Konversi satuan per item    |
| `stock_repacks`             | Repack stok                 |

### Route Naming

- Prefix: `dashboard.resto.resep`
- Route penting:
    - `dashboard.resto.resep` — Dashboard
    - `dashboard.resto.resep.recipe` — Daftar resep
    - `dashboard.resto.resep.recipe.detail` — Detail resep (`/resep/recipe/{id}`)
    - `dashboard.resto.konversi-satuan` — Konversi satuan
    - `dashboard.resto.repack` — Repack stok
