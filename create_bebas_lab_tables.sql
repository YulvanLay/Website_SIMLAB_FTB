CREATE TABLE IF NOT EXISTS bebas_laboratorium (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    pelanggan_id BIGINT UNSIGNED NOT NULL,
    laboratorium_id BIGINT UNSIGNED NOT NULL,
    form_url VARCHAR(255) NULLABLE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (pelanggan_id) REFERENCES pelanggans(id) ON DELETE CASCADE,
    FOREIGN KEY (laboratorium_id) REFERENCES laboratoriums(id) ON DELETE CASCADE,
    INDEX idx_pelanggan (pelanggan_id),
    INDEX idx_laboratorium (laboratorium_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS bebas_laboratorium_checklists (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO bebas_laboratorium (pelanggan_id, laboratorium_id, form_url, created_at, updated_at) 
VALUES 
    (1, 1, 'https://form.kampus.ac.id/bebas-lab/lab-1', NOW(), NOW()),
    (1, 2, 'https://form.kampus.ac.id/bebas-lab/lab-2', NOW(), NOW()),
    (1, 3, 'https://form.kampus.ac.id/bebas-lab/lab-3', NOW(), NOW());

INSERT INTO bebas_laboratorium_checklists (bebas_laboratorium_id, checklist_number, checklist_text, created_at, updated_at) 
VALUES 
    (1, 1, 'Bebas Pinjaman peralatan dan kunci loker', NOW(), NOW()),
    (1, 2, 'Sudah membersihkan biakan bakteri (Coldorom, CTR)', NOW(), NOW()),
    (1, 3, 'Sudah membayar bahan kimia dan media yang digunakan', NOW(), NOW()),
    (1, 4, 'Alat gelas dalam keadaan bersih dari label atau coret-coretan', NOW(), NOW()),
    (1, 5, 'Alat yang pecah atau rusak telah diganti', NOW(), NOW()),
    (2, 1, 'Bebas Pinjaman peralatan dan kunci loker', NOW(), NOW()),
    (2, 2, 'Sudah membersihkan biakan bakteri (Coldorom, CTR)', NOW(), NOW()),
    (2, 3, 'Sudah membayar bahan kimia dan media yang digunakan', NOW(), NOW()),
    (2, 4, 'Alat gelas dalam keadaan bersih dari label atau coret-coretan', NOW(), NOW()),
    (2, 5, 'Alat yang pecah atau rusak telah diganti', NOW(), NOW()),
    (3, 1, 'Bebas Pinjaman peralatan dan kunci loker', NOW(), NOW()),
    (3, 2, 'Sudah membersihkan biakan bakteri (Coldorom, CTR)', NOW(), NOW()),
    (3, 3, 'Sudah membayar bahan kimia dan media yang digunakan', NOW(), NOW()),
    (3, 4, 'Alat gelas dalam keadaan bersih dari label atau coret-coretan', NOW(), NOW()),
    (3, 5, 'Alat yang pecah atau rusak telah diganti', NOW(), NOW());
