# Dokumentasi Fitur Bebas Laboratorium

## Daftar Isi
1. [Deskripsi Fitur](#deskripsi-fitur)
2. [Database Schema](#database-schema)
3. [Struktur File](#struktur-file)
4. [Installation & Setup](#installation--setup)
5. [Cara Penggunaan](#cara-penggunaan)
6. [API Endpoints](#api-endpoints)
7. [User Roles & Permissions](#user-roles--permissions)

---

## Deskripsi Fitur

Fitur **Bebas Laboratorium** adalah modul yang memungkinkan pelanggan untuk mengajukan pernyataan bebas laboratorium. Fitur ini menampilkan checklist dari setiap laboratorium yang harus disetujui oleh:
- **Laboran**: Memeriksa dan menyetujui checklist awal
- **Kalab (Kepala Laboratorium)**: Memberikan approval akhir

### Checklist Items (5 Items)
1. Bebas Pinjaman peralatan dan kunci loker
2. Sudah membersihkan biakan bakteri (Coldorom, CTR)
3. Sudah membayar bahan kimia dan media yang digunakan
4. Alat gelas dalam keadaan bersih dari label atau coret-coretan
5. Alat yang pecah atau rusak telah diganti

---

## Database Schema

### Tabel: `bebas_laboratorium`
Menyimpan data bebas laboratorium per pelanggan dan laboratorium

```sql
CREATE TABLE bebas_laboratorium (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    pelanggan_id BIGINT UNSIGNED NOT NULL,
    laboratorium_id BIGINT UNSIGNED NOT NULL,
    form_url VARCHAR(255) NULLABLE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (pelanggan_id) REFERENCES pelanggans(id),
    FOREIGN KEY (laboratorium_id) REFERENCES laboratoriums(id),
    INDEX idx_pelanggan (pelanggan_id),
    INDEX idx_laboratorium (laboratorium_id)
);
```

**Kolom:**
- `id`: Primary key
- `pelanggan_id`: Referensi ke tabel `pelanggans`
- `laboratorium_id`: Referensi ke tabel `laboratoriums`
- `form_url`: URL untuk form bebas lab dari kampus (optional)
- `created_at`: Timestamp pembuatan
- `updated_at`: Timestamp perubahan terakhir

### Tabel: `bebas_laboratorium_checklists`
Menyimpan status checklist untuk setiap item dari laboran dan kalab

```sql
CREATE TABLE bebas_laboratorium_checklists (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    bebas_laboratorium_id BIGINT UNSIGNED NOT NULL,
    checklist_number INT NOT NULL,
    checklist_text VARCHAR(255) NOT NULL,
    laboran_checked TINYINT(1) DEFAULT 0,
    laboran_checked_at TIMESTAMP NULLABLE,
    kalab_checked TINYINT(1) DEFAULT 0,
    kalab_checked_at TIMESTAMP NULLABLE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (bebas_laboratorium_id) REFERENCES bebas_laboratorium(id) ON DELETE CASCADE,
    INDEX idx_bebas_lab (bebas_laboratorium_id)
);
```

**Kolom:**
- `id`: Primary key
- `bebas_laboratorium_id`: Referensi ke tabel `bebas_laboratorium`
- `checklist_number`: Nomor checklist (1-5)
- `checklist_text`: Isi checklist item
- `laboran_checked`: Status approval dari laboran (0/1)
- `laboran_checked_at`: Waktu approval laboran
- `kalab_checked`: Status approval dari kalab (0/1)
- `kalab_checked_at`: Waktu approval kalab
- `created_at`: Timestamp pembuatan
- `updated_at`: Timestamp perubahan terakhir

---

## Struktur File

```
app/
├── BebasLaboratorium.php                    # Model untuk bebas laboratorium
├── BebasLaboratoriumChecklist.php           # Model untuk checklist items
├── Http/Controllers/
│   └── BebasLaboratoriumController.php      # Controller untuk logika

database/
├── migrations/
│   ├── 2024_05_01_create_bebas_laboratorium_table.php
│   └── 2024_05_02_create_bebas_laboratorium_checklists_table.php
└── seeds/
    └── BebasLaboratoriumSeeder.php          # Seeder untuk data awal

resources/views/bebas-lab/
└── daftar.blade.php                        # View untuk daftar bebas lab

routes/
└── web.php                                 # Routes untuk bebas laboratorium
```

---

## Installation & Setup

### 1. Run Migrations

```bash
php artisan migrate
```

### 2. Membuat Data Bebas Laboratorium

Ada beberapa cara untuk membuat data:

**Cara 1: Menggunakan Tinker (Interactive)**
```bash
php artisan tinker
```

Kemudian di dalam tinker:
```php
$pelanggan = \App\Pelanggan::first(); // Ambil pelanggan
$laboratoriums = \App\Laboratorium::all(); // Ambil semua laboratorium

foreach ($laboratoriums as $lab) {
    $bebas = \App\BebasLaboratorium::create([
        'pelanggan_id' => $pelanggan->id,
        'laboratorium_id' => $lab->id,
        'form_url' => 'https://link-form-kampus.com' // Optional
    ]);
    
    // Create checklist items
    $checklistTexts = [
        'Bebas Pinjaman peralatan dan kunci loker',
        'Sudah membersihkan biakan bakteri (Coldorom, CTR)',
        'Sudah membayar bahan kimia dan media yang digunakan',
        'Alat gelas dalam keadaan bersih dari label atau coret-coretan',
        'Alat yang pecah atau rusak telah diganti'
    ];
    
    foreach ($checklistTexts as $index => $text) {
        \App\BebasLaboratoriumChecklist::create([
            'bebas_laboratorium_id' => $bebas->id,
            'checklist_number' => $index + 1,
            'checklist_text' => $text
        ]);
    }
}
```

**Cara 2: Menggunakan Migration Seeder**
1. Edit `database/seeds/BebasLaboratoriumSeeder.php`
2. Uncomment section yang membuat data otomatis
3. Jalankan seeder

---

## Cara Penggunaan

### 1. Akses Halaman Bebas Lab
```
URL: /bebas-lab/
```

### 2. Pilih Pelanggan
- Klik dropdown "Pelanggan"
- Pilih salah satu pelanggan

### 3. Lihat Data
Tabel akan menampilkan semua laboratorium dengan 4 kolom action:

#### A. Preview
- Klik tombol "Preview" untuk melihat checklist dari perspektif pelanggan
- Checklist akan ditampilkan dalam bentuk read-only

#### B. Acc Laboran
- Klik tombol "Acc Laboran" 
- Laboran dapat memilih/uncheck item checklist
- Klik "Simpan" untuk menyimpan status

#### C. Acc Kalab
- Klik tombol "Acc Kalab"
- Kalab dapat memilih/uncheck item checklist
- Klik "Simpan" untuk menyimpan status

#### D. Form Bebas Lab
- Klik tombol "Form" untuk membuka form dari kampus
- Jika form_url belum diset, akan muncul pesan info

---

## API Endpoints

### 1. GET `/bebas-lab/`
Menampilkan halaman bebas laboratorium

**Response:** HTML page

### 2. GET `/bebas-lab/data`
Mengambil data bebas laboratorium berdasarkan pelanggan

**Parameters:**
- `pelanggan_id` (required): ID pelanggan

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "laboratorium_id": 1,
            "laboratorium_name": "Lab Biologi",
            "form_url": "https://...",
            "laboran_complete": false,
            "kalab_complete": false,
            "checklists": [...]
        }
    ]
}
```

### 3. GET `/bebas-lab/pelanggan-list`
Mengambil list semua pelanggan

**Response:**
```json
{
    "data": [
        {
            "id": 1,
            "nama_pelanggan": "Pelanggan A"
        }
    ]
}
```

### 4. GET `/bebas-lab/checklists/{id}`
Mengambil checklist items dari bebas laboratorium tertentu

**Parameters:**
- `id` (required): ID bebas laboratorium

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "bebas_laboratorium_id": 1,
            "checklist_number": 1,
            "checklist_text": "Bebas Pinjaman peralatan dan kunci loker",
            "laboran_checked": false,
            "laboran_checked_at": null,
            "kalab_checked": false,
            "kalab_checked_at": null,
            "created_at": "2024-05-01 10:00:00",
            "updated_at": "2024-05-01 10:00:00"
        }
    ]
}
```

### 5. POST `/bebas-lab/laboran-checklist/update`
Update status checklist laboran

**Parameters:**
- `checklist_id` (required): ID checklist item
- `is_checked` (required): boolean (true/false)

**Response:**
```json
{
    "success": true,
    "message": "Checklist laboran berhasil diperbarui",
    "data": {...}
}
```

### 6. POST `/bebas-lab/kalab-checklist/update`
Update status checklist kalab

**Parameters:**
- `checklist_id` (required): ID checklist item
- `is_checked` (required): boolean (true/false)

**Response:**
```json
{
    "success": true,
    "message": "Checklist kalab berhasil diperbarui",
    "data": {...}
}
```

---

## User Roles & Permissions

### 1. Pelanggan
- **Akses**: Melihat status bebas laboratorium
- **Permissions**: Read-only untuk preview

### 2. Laboran
- **Akses**: Tab "Acc Laboran"
- **Permissions**: Dapat memilih/uncheck checklist dan simpan

### 3. Kalab (Kepala Laboratorium)
- **Akses**: Tab "Acc Kalab"
- **Permissions**: Dapat memilih/uncheck checklist dan simpan

### 4. Admin
- **Akses**: Full access
- **Permissions**: Dapat melakukan semua operasi

---

## Model Relationships

### BebasLaboratorium
```php
public function pelanggan()        // Belongs to Pelanggan
public function laboratorium()     // Belongs to Laboratorium
public function checklists()       // Has many BebasLaboratoriumChecklist
public function laboran()          // Get laboran dari laboratorium
```

### BebasLaboratoriumChecklist
```php
public function bebasLaboratorium() // Belongs to BebasLaboratorium
```

---

## Helper Methods

### BebasLaboratorium::isLaboranChecklistComplete()
Mengecek apakah semua checklist laboran sudah disetujui

```php
$bebas = BebasLaboratorium::find(1);
if ($bebas->isLaboranChecklistComplete()) {
    // Semua checklist laboran complete
}
```

### BebasLaboratorium::isKalabChecklistComplete()
Mengecek apakah semua checklist kalab sudah disetujui

```php
$bebas = BebasLaboratorium::find(1);
if ($bebas->isKalabChecklistComplete()) {
    // Semua checklist kalab complete
}
```

---

## Notes & Best Practices

1. **Migration Order**: Jalankan migration untuk `bebas_laboratorium` terlebih dahulu sebelum `bebas_laboratorium_checklists`
2. **Data Consistency**: Gunakan transaction untuk membuat bebas laboratorium dengan semua checklist items
3. **Soft Deletes**: Pertimbangkan untuk menambahkan soft deletes jika diperlukan audit trail
4. **Validation**: Tambahkan validation di controller untuk input data
5. **Authorization**: Tambahkan policy/gate untuk memastikan user hanya bisa access data yang relevan dengan role mereka

---

## Troubleshooting

### Error: Foreign Key Constraint
- Pastikan tabel `pelanggans` dan `laboratoriums` sudah ada dan punya data
- Periksa urutan migration

### Data tidak muncul di table
- Pastikan data bebas_laboratorium dengan checklists sudah dibuat
- Cek query di browser console (Network tab)

### Checklist tidak tersimpan
- Pastikan CSRF token ada di form
- Cek response dari API di browser console
- Pastikan user punya permission untuk update

---

## Future Enhancements

1. [ ] Tambahkan role-based access control (RBAC)
2. [ ] Email notification saat approval
3. [ ] History/audit log untuk setiap approval
4. [ ] Bulk operations untuk multiple laboratories
5. [ ] Export to PDF/Excel
6. [ ] Deadline/reminder untuk pending approvals

