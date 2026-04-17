Berdasarkan timeline proyek yang sedang kamu jalankan, kamu saat ini berada di fase **Recipe master \+ BOM CRUD** yang dijadwalkan pada **24 April 2026**. Sesuai alur dan aturan yang kamu minta, berikut adalah PRD singkat untuk modul manajemen resep (Recipe/BOM).

---

## **PRD: Recipe & Bill of Materials (BOM) Management**

### **1\. Tujuan**

Membangun sistem manajemen resep yang memungkinkan pengguna menyusun komposisi bahan untuk menu tunggal (Single Menu), barang setengah jadi (Semi-finished goods/Preparation), maupun menu paket (Additional/Bundling) dengan dukungan versioning.

### **2\. Struktur Data & Aturan Main**

* **Entity Relationship:** Satu Menu dapat memiliki beberapa versi resep, namun hanya satu yang berstatus is\_active \= true.  
* **Versi & Keterangan:** Setiap resep wajib memiliki label versi (misal: "V1 \- Standar", "V2 \- Promo Ramadan") untuk mempermudah tracking perubahan cost.  
* **Satuan (UOM):** Input *quantity* harus mendukung konversi satuan (misal: resep menggunakan 'gram' sementara stok masuk dalam 'kg').

### **3\. Alur Penggunaan (User Flow)**

1. **Pilih Menu:** User mencari dan memilih menu/item dari Master Inventory.  
2. **Identifikasi Tipe:** Menentukan apakah ini menu final atau *preparation* (bumbu base).  
3. **Input Bahan:** Memilih bahan baku dari inventory atau memilih hasil *preparation* lain (Nested BOM).  
4. **Isi Qty:** Memasukkan jumlah kebutuhan tiap bahan.  
5. **Simpan & Aktivasi:** Mengisi keterangan versi dan menandai apakah resep ini langsung aktif.

---

### **4\. Implementasi Studi Kasus**

#### **Kasus 1: Preparation (Bumbu Base)**

Modul ini harus mendukung **Nested BOM** (bahan yang menghasilkan barang baru untuk stok).

* **Output:** Base Nasi Goreng (Semi-finished).  
* **Komposisi:**  
  * Bawang Merah: 500 gr.  
  * Garam: 50 gr.  
  * *Proses:* Diblender (Hasil akhir masuk ke stok Base Nasi Goreng).

#### **Kasus 2: Menu Additional (Bundling)**

Modul harus mampu menangani **Semi-finished bertumpuk**.

* **Menu:** Nasi Goreng Rendang.  
* **Komposisi:**  
  * Nasi Putih: 1 porsi.  
  * **Base Nasi Goreng** (Hasil Kasus 1): 20 gr.  
  * **Rendang** (Menu/Item terpisah): 1 pcs.

---

### **5\. Rencana Validasi & Testing**

Sesuai timeline, setelah pengembangan CRUD resep selesai, kamu akan masuk ke tahap:

* **Nested BOM Logic (25 April):** Memastikan sistem bisa membaca resep di dalam resep.  
* **Production Flow (26 April):** Menguji proses produksi barang setengah jadi (bumbu base).  
* **Consume Logic (28 April):** Memastikan stok bahan otomatis berkurang saat menu diproduksi atau dijual.

---

