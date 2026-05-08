# Database Schema untuk Fitur Bebas Lab

## Tabel: bebas_laboratorium
Menyimpan data bebas laboratorium per pelanggan per laboratorium

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

## Tabel: bebas_laboratorium_checklists
Menyimpan checklist review laboran dan kalab

```sql
CREATE TABLE bebas_laboratorium_checklists (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    bebas_laboratorium_id BIGINT UNSIGNED NOT NULL,
    checklist_number INT NOT NULL, -- 1-5
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

## Data Checklist Items (5 items)
1. Bebas Pinjaman peralatan dan kunci loker
2. Sudah membersihkan biakan bakteri (Coldorom, CTR)
3. Sudah membayar bahan kimia dan media yang digunakan
4. Alat gelas dalam keadaan bersih dari label atau coret-coretan
5. Alat yang pecah atau rusak telah diganti

## Relasi Tabel
- Pelanggan (existing) ←→ Bebas Laboratorium (1:N)
- Laboratorium (existing) ←→ Bebas Laboratorium (1:N)
- Bebas Laboratorium ←→ Bebas Laboratorium Checklists (1:N)
