# Resto Dashboard - Dokumentasi Perubahan

## Ringkasan

Tampilan dashboard resto telah dirapikan sesuai dengan mind map "Sains De Resto". Perubahan meliputi pengelompokan menu menjadi section-section yang terstruktur, penambahan ikon emoji pada setiap tile, dan penanganan tile yang belum tersedia.

**File yang diubah:**
- `resources/views/livewire/dashboard/resto-dashboard.blade.php`

## Struktur Baru Dashboard

Dashboard kini terbagi menjadi **10 section** sesuai mind map:

### 1. POS (Orange)
| Tile | Route | Status |
|------|-------|--------|
| Order | `dashboard.resto.menu-pos` | ✅ Aktif |
| List Order | `dashboard.resto.orders` | ✅ Aktif |
| Kitchen Display | `dashboard.resto.chef` | ✅ Aktif |
| Kasir | `dashboard.resto.cashier` | ✅ Aktif |
| Reservasi | - | ❌ Segera Hadir |
| Daily Closing | - | ❌ Segera Hadir |

### 2. Dashboard (Blue)
| Tile | Route | Status |
|------|-------|--------|
| Ringkasan Penjualan | - | ❌ Segera Hadir |
| Penjualan per Kategori | - | ❌ Segera Hadir |
| Kategori Terlaris | - | ❌ Segera Hadir |
| Stock Alert | - | ❌ Segera Hadir |

### 3. Master Data (Green)
| Tile | Route | Status |
|------|-------|--------|
| Bahan Baku | `dashboard.resto.item` | ✅ Aktif |
| Kategori | `dashboard.resto.kategori` | ✅ Aktif |
| Satuan | `dashboard.resto.satuan` | ✅ Aktif |
| Konversi Satuan | `dashboard.resto.konversi-satuan` | ✅ Aktif |
| Vendor | `dashboard.resto.vendor` | ✅ Aktif |
| Lokasi | `dashboard.resto.lokasi` | ✅ Aktif |
| Manajemen Meja | `dashboard.resto.meja` | ✅ Aktif |
| Customer | - | ❌ Segera Hadir |

### 4. Inventory (Green-600)
| Tile | Route | Status |
|------|-------|--------|
| Stock | `dashboard.resto.core-stock` | ✅ Aktif |
| Stock Kritis | `dashboard.resto.stock-minimal` | ✅ Aktif |
| Stock Movement | `dashboard.resto.movement-internal` | ✅ Aktif |
| Stock Opname | - | ❌ Segera Hadir |
| Waste | - | ❌ Segera Hadir |

### 5. Procurement (Teal)
| Tile | Route | Status |
|------|-------|--------|
| PR | `dashboard.resto.purchase-request` | ✅ Aktif |
| PO | `dashboard.resto.purchase-order` | ✅ Aktif |
| Goods Receipt | - | ❌ Segera Hadir |
| DO | `dashboard.resto.direct-order` | ✅ Aktif |
| Invoice Vendor | - | ❌ Segera Hadir |

### 6. Recipe & Production (Green)
| Tile | Route | Status |
|------|-------|--------|
| Resep Menu | `dashboard.resto.resep-menu` | ✅ Aktif |
| Resep Semi-Finished | `dashboard.resto.resep.recipe` | ✅ Aktif |
| Additional Condition | - | ❌ Segera Hadir |

### 7. Daftar Menu (Green)
| Tile | Route | Status |
|------|-------|--------|
| HJP | `dashboard.resto.menu` | ✅ Aktif |

### 8. Laporan (Purple)
| Tile | Route | Status |
|------|-------|--------|
| Rekap Harian | - | ❌ Segera Hadir |
| Laporan Penjualan | - | ❌ Segera Hadir |
| Laporan Stock | - | ❌ Segera Hadir |
| Laporan Waste | - | ❌ Segera Hadir |
| Laporan Profit | - | ❌ Segera Hadir |
| Laporan Keuangan | - | ❌ Segera Hadir |

### 9. Setting (Gray)
| Tile | Route | Status |
|------|-------|--------|
| User Management | - | ❌ Segera Hadir |
| Role | - | ❌ Segera Hadir |

### 10. Lainnya (Amber/Purple/Green) - Tile Lama
| Tile | Route | Status |
|------|-------|--------|
| Makan Siang Karyawan | `dashboard.resto.employee-lunch` | ✅ Aktif |
| Riwayat Makan Siang | `dashboard.resto.employee-lunch.report` | ✅ Aktif |
| Costing & Finance Hooks | `dashboard.resto.master` | ✅ Aktif |
| Master Menu | `dashboard.resto.menu` | ✅ Aktif |

## Perubahan Detail

### Sebelum
- 13 tile tanpa pengelompokan
- Tidak ada section header
- Tidak ada ikon
- Tile "Costing & Finance Hooks" duplikat route dengan "Master Data"

### Sesudah
- 10 section dengan header berwarna
- Setiap tile memiliki ikon emoji
- Tile yang belum tersedia ditampilkan dalam keadaan disabled (abu-abu, opacity 60%)
- Tile lama dipindahkan ke section "Lainnya"
- Grid layout: `grid-cols-1 md:grid-cols-4 lg:grid-cols-5`
- Tinggi tile: `h-36` (sebelumnya `h-40`)
- Layout tile: `flex-col` dengan ikon di atas dan teks di bawah

## Penanganan Tile "Segera Hadir"

Tile yang belum memiliki route ditampilkan dengan:
- Background: `bg-gray-300`
- Opacity: `opacity-60`
- Cursor: `cursor-not-allowed`
- Label tambahan: "Segera Hadir" di bawah nama tile
- Bukan elemen `<a>`, melainkan `<div>` biasa

## Emoji Mapping

| Section | Tile | Emoji |
|---------|------|-------|
| POS | Order | 🛒 |
| POS | List Order | 📋 |
| POS | Kitchen Display | 👨‍ |
| POS | Kasir | 💰 |
| POS | Reservasi | 📅 |
| POS | Daily Closing | 🔒 |
| Dashboard | Ringkasan Penjualan | 📊 |
| Dashboard | Penjualan per Kategori | 📈 |
| Dashboard | Kategori Terlaris | 🏆 |
| Dashboard | Stock Alert | ⚠️ |
| Master Data | Bahan Baku | 🥬 |
| Master Data | Kategori | 🏷️ |
| Master Data | Satuan | 📏 |
| Master Data | Konversi Satuan | 🔄 |
| Master Data | Vendor | 🏭 |
| Master Data | Lokasi | 📍 |
| Master Data | Manajemen Meja | 🪑 |
| Master Data | Customer | 👥 |
| Inventory | Stock | 📦 |
| Inventory | Stock Kritis | 🔴 |
| Inventory | Stock Movement | 🚚 |
| Inventory | Stock Opname | 📝 |
| Inventory | Waste | 🗑️ |
| Procurement | PR | 📄 |
| Procurement | PO | 📋 |
| Procurement | Goods Receipt | 📥 |
| Procurement | DO |  |
| Procurement | Invoice Vendor | 💳 |
| Recipe & Production | Resep Menu | 📖 |
| Recipe & Production | Resep Semi-Finished | 🧪 |
| Recipe & Production | Additional Condition | ⚙️ |
| Daftar Menu | HJP | 📋 |
| Laporan | Rekap Harian | 📅 |
| Laporan | Laporan Penjualan | 📊 |
| Laporan | Laporan Stock | 📦 |
| Laporan | Laporan Waste | 🗑️ |
| Laporan | Laporan Profit | 💰 |
| Laporan | Laporan Keuangan | 🏦 |
| Setting | User Management | 👤 |
| Setting | Role | 🔑 |
| Lainnya | Makan Siang Karyawan | 🍱 |
| Lainnya | Riwayat Makan Siang | 📋 |
| Lainnya | Costing & Finance Hooks | 💲 |
| Lainnya | Master Menu | 📋 |

## Warna Section

| Section | Warna Border | Warna Tile Aktif |
|---------|-------------|-----------------|
| POS | `border-orange-500` | `bg-orange-500` |
| Dashboard | `border-blue-500` | `bg-gray-300` (disabled) |
| Master Data | `border-green-500` | `bg-green-500` |
| Inventory | `border-green-600` | `bg-green-600` |
| Procurement | `border-teal-500` | `bg-teal-500` |
| Recipe & Production | `border-green-500` | `bg-green-500` |
| Daftar Menu | `border-green-500` | `bg-green-500` |
| Laporan | `border-purple-500` | `bg-gray-300` (disabled) |
| Setting | `border-gray-500` | `bg-gray-300` (disabled) |
| Lainnya | `border-amber-500` | Mixed |

## Catatan

- Tile yang belum memiliki route tetap ditampilkan sebagai placeholder untuk perencanaan fitur mendatang
- Tile lama (Makan Siang Karyawan, dll) dipertahankan di section "Lainnya" untuk backward compatibility
- Format kode telah dijalankan dengan `vendor/bin/pint`
