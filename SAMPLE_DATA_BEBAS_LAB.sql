-- =====================================================
-- Sample SQL Data untuk Testing Bebas Laboratorium
-- =====================================================

-- Asumsikan sudah ada data di tabel:
-- - pelanggans (dengan minimal 1 data)
-- - laboratoriums (dengan minimal 3 data)

-- 1. Insert data ke bebas_laboratorium
-- Ambil 1 pelanggan dan hubungkan dengan semua laboratorium

INSERT INTO bebas_laboratorium (pelanggan_id, laboratorium_id, form_url, created_at, updated_at) 
VALUES 
    (1, 1, 'https://form.kampus.ac.id/bebas-lab/lab-1', NOW(), NOW()),
    (1, 2, 'https://form.kampus.ac.id/bebas-lab/lab-2', NOW(), NOW()),
    (1, 3, 'https://form.kampus.ac.id/bebas-lab/lab-3', NOW(), NOW()),
    (2, 1, NULL, NOW(), NOW()),
    (2, 2, NULL, NOW(), NOW());

-- 2. Insert data ke bebas_laboratorium_checklists
-- Untuk setiap bebas_laboratorium, buat 5 checklist items

-- Untuk bebas_laboratorium id=1
INSERT INTO bebas_laboratorium_checklists (bebas_laboratorium_id, checklist_number, checklist_text, created_at, updated_at) 
VALUES 
    (1, 1, 'Bebas Pinjaman peralatan dan kunci loker', NOW(), NOW()),
    (1, 2, 'Sudah membersihkan biakan bakteri (Coldorom, CTR)', NOW(), NOW()),
    (1, 3, 'Sudah membayar bahan kimia dan media yang digunakan', NOW(), NOW()),
    (1, 4, 'Alat gelas dalam keadaan bersih dari label atau coret-coretan', NOW(), NOW()),
    (1, 5, 'Alat yang pecah atau rusak telah diganti', NOW(), NOW());

-- Untuk bebas_laboratorium id=2
INSERT INTO bebas_laboratorium_checklists (bebas_laboratorium_id, checklist_number, checklist_text, created_at, updated_at) 
VALUES 
    (2, 1, 'Bebas Pinjaman peralatan dan kunci loker', NOW(), NOW()),
    (2, 2, 'Sudah membersihkan biakan bakteri (Coldorom, CTR)', NOW(), NOW()),
    (2, 3, 'Sudah membayar bahan kimia dan media yang digunakan', NOW(), NOW()),
    (2, 4, 'Alat gelas dalam keadaan bersih dari label atau coret-coretan', NOW(), NOW()),
    (2, 5, 'Alat yang pecah atau rusak telah diganti', NOW(), NOW());

-- Untuk bebas_laboratorium id=3
INSERT INTO bebas_laboratorium_checklists (bebas_laboratorium_id, checklist_number, checklist_text, created_at, updated_at) 
VALUES 
    (3, 1, 'Bebas Pinjaman peralatan dan kunci loker', NOW(), NOW()),
    (3, 2, 'Sudah membersihkan biakan bakteri (Coldorom, CTR)', NOW(), NOW()),
    (3, 3, 'Sudah membayar bahan kimia dan media yang digunakan', NOW(), NOW()),
    (3, 4, 'Alat gelas dalam keadaan bersih dari label atau coret-coretan', NOW(), NOW()),
    (3, 5, 'Alat yang pecah atau rusak telah diganti', NOW(), NOW());

-- Untuk bebas_laboratorium id=4
INSERT INTO bebas_laboratorium_checklists (bebas_laboratorium_id, checklist_number, checklist_text, created_at, updated_at) 
VALUES 
    (4, 1, 'Bebas Pinjaman peralatan dan kunci loker', NOW(), NOW()),
    (4, 2, 'Sudah membersihkan biakan bakteri (Coldorom, CTR)', NOW(), NOW()),
    (4, 3, 'Sudah membayar bahan kimia dan media yang digunakan', NOW(), NOW()),
    (4, 4, 'Alat gelas dalam keadaan bersih dari label atau coret-coretan', NOW(), NOW()),
    (4, 5, 'Alat yang pecah atau rusak telah diganti', NOW(), NOW());

-- Untuk bebas_laboratorium id=5
INSERT INTO bebas_laboratorium_checklists (bebas_laboratorium_id, checklist_number, checklist_text, created_at, updated_at) 
VALUES 
    (5, 1, 'Bebas Pinjaman peralatan dan kunci loker', NOW(), NOW()),
    (5, 2, 'Sudah membersihkan biakan bakteri (Coldorom, CTR)', NOW(), NOW()),
    (5, 3, 'Sudah membayar bahan kimia dan media yang digunakan', NOW(), NOW()),
    (5, 4, 'Alat gelas dalam keadaan bersih dari label atau coret-coretan', NOW(), NOW()),
    (5, 5, 'Alat yang pecah atau rusak telah diganti', NOW(), NOW());

-- =====================================================
-- Query untuk testing
-- =====================================================

-- 1. Lihat semua bebas laboratorium dengan checklist
SELECT 
    bl.id as bebas_id,
    p.nama_pelanggan,
    lb.nama_laboratorium,
    COUNT(blc.id) as total_checklist,
    SUM(CASE WHEN blc.laboran_checked = 1 THEN 1 ELSE 0 END) as laboran_checked_count,
    SUM(CASE WHEN blc.kalab_checked = 1 THEN 1 ELSE 0 END) as kalab_checked_count
FROM bebas_laboratorium bl
JOIN pelanggans p ON bl.pelanggan_id = p.id
JOIN laboratoriums lb ON bl.laboratorium_id = lb.id
LEFT JOIN bebas_laboratorium_checklists blc ON bl.id = blc.bebas_laboratorium_id
GROUP BY bl.id, p.nama_pelanggan, lb.nama_laboratorium;

-- 2. Lihat checklist dari bebas laboratorium tertentu
SELECT * FROM bebas_laboratorium_checklists 
WHERE bebas_laboratorium_id = 1 
ORDER BY checklist_number ASC;

-- 3. Lihat semua bebas laboratorium untuk pelanggan tertentu
SELECT * FROM bebas_laboratorium 
WHERE pelanggan_id = 1;

-- 4. Update checklist laboran sebagai approved
UPDATE bebas_laboratorium_checklists 
SET laboran_checked = 1, laboran_checked_at = NOW()
WHERE bebas_laboratorium_id = 1 AND checklist_number = 1;

-- 5. Update checklist kalab sebagai approved
UPDATE bebas_laboratorium_checklists 
SET kalab_checked = 1, kalab_checked_at = NOW()
WHERE bebas_laboratorium_id = 1;

-- 6. Cek status approval lengkap
SELECT 
    bl.id,
    p.nama_pelanggan,
    lb.nama_laboratorium,
    CASE 
        WHEN COUNT(blc.id) = SUM(CASE WHEN blc.laboran_checked = 1 THEN 1 ELSE 0 END) 
        THEN 'Laboran Approved' 
        ELSE 'Pending Laboran' 
    END as laboran_status,
    CASE 
        WHEN COUNT(blc.id) = SUM(CASE WHEN blc.kalab_checked = 1 THEN 1 ELSE 0 END) 
        THEN 'Kalab Approved' 
        ELSE 'Pending Kalab' 
    END as kalab_status
FROM bebas_laboratorium bl
JOIN pelanggans p ON bl.pelanggan_id = p.id
JOIN laboratoriums lb ON bl.laboratorium_id = lb.id
LEFT JOIN bebas_laboratorium_checklists blc ON bl.id = blc.bebas_laboratorium_id
GROUP BY bl.id;
