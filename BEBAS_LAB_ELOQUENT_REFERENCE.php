<?php

/**
 * REFERENSI ELOQUENT QUERIES
 * File ini berisi kumpulan useful queries untuk fitur Bebas Laboratorium
 */

// =====================================================
// 1. GET DATA
// =====================================================

// Ambil bebas laboratorium dengan relasi
$bebas = BebasLaboratorium::with(['pelanggan', 'laboratorium', 'checklists'])->find(1);

// Ambil semua bebas laboratorium untuk pelanggan tertentu
$bebasList = BebasLaboratorium::where('pelanggan_id', $pelangganId)
    ->with(['laboratorium', 'checklists'])
    ->get();

// Ambil bebas laboratorium dengan checklist yang belum diapprove laboran
$pendingLaboran = BebasLaboratorium::whereHas('checklists', function ($q) {
    $q->where('laboran_checked', false);
})->with(['laboratorium', 'checklists'])->get();

// Ambil checklist items untuk bebas lab tertentu
$checklists = BebasLaboratoriumChecklist::where('bebas_laboratorium_id', $bebasLabId)
    ->orderBy('checklist_number')
    ->get();

// =====================================================
// 2. COUNT & CHECK STATUS
// =====================================================

// Hitung berapa checklist yang sudah diapprove laboran
$laboranApprovedCount = BebasLaboratoriumChecklist::where('bebas_laboratorium_id', $bebasLabId)
    ->where('laboran_checked', true)
    ->count();

// Hitung berapa checklist yang sudah diapprove kalab
$kalabApprovedCount = BebasLaboratoriumChecklist::where('bebas_laboratorium_id', $bebasLabId)
    ->where('kalab_checked', true)
    ->count();

// Check apakah semua laboran checklist complete
$isLaboranComplete = BebasLaboratoriumChecklist::where('bebas_laboratorium_id', $bebasLabId)
    ->where('laboran_checked', false)
    ->count() === 0;

// Check apakah semua kalab checklist complete
$isKalabComplete = BebasLaboratoriumChecklist::where('bebas_laboratorium_id', $bebasLabId)
    ->where('kalab_checked', false)
    ->count() === 0;

// =====================================================
// 3. CREATE & UPDATE
// =====================================================

// Buat bebas laboratorium baru dengan checklist
$bebas = BebasLaboratorium::create([
    'pelanggan_id' => $pelangganId,
    'laboratorium_id' => $laboratoriumId,
    'form_url' => 'https://form.kampus.ac.id/...',
]);

// Buat checklist items untuk bebas lab
$checklistTexts = [
    'Bebas Pinjaman peralatan dan kunci loker',
    'Sudah membersihkan biakan bakteri (Coldorom, CTR)',
    'Sudah membayar bahan kimia dan media yang digunakan',
    'Alat gelas dalam keadaan bersih dari label atau coret-coretan',
    'Alat yang pecah atau rusak telah diganti'
];

foreach ($checklistTexts as $index => $text) {
    BebasLaboratoriumChecklist::create([
        'bebas_laboratorium_id' => $bebas->id,
        'checklist_number' => $index + 1,
        'checklist_text' => $text,
    ]);
}

// Update checklist item (laboran approval)
$checklist = BebasLaboratoriumChecklist::find($checklistId);
$checklist->update([
    'laboran_checked' => true,
    'laboran_checked_at' => now(),
]);

// Update checklist item (kalab approval)
$checklist = BebasLaboratoriumChecklist::find($checklistId);
$checklist->update([
    'kalab_checked' => true,
    'kalab_checked_at' => now(),
]);

// =====================================================
// 4. DELETE
// =====================================================

// Hapus bebas laboratorium dan semua checklistnya (cascade)
BebasLaboratorium::find($bebasLabId)->delete();

// Hapus single checklist item
BebasLaboratoriumChecklist::find($checklistId)->delete();

// =====================================================
// 5. DENGAN FILTER & CONDITION
// =====================================================

// Ambil bebas lab untuk laboratorium tertentu
$bebasLabsPerLaboratorium = BebasLaboratorium::where('laboratorium_id', $laboratoriumId)
    ->with('pelanggan')
    ->get();

// Ambil bebas lab yang form_url-nya sudah diisi
$bebasLabsWithForm = BebasLaboratorium::whereNotNull('form_url')
    ->get();

// Ambil checklist yang belum diapprove keduanya
$pendingBoth = BebasLaboratoriumChecklist::where(function ($q) {
    $q->where('laboran_checked', false)
        ->orWhere('kalab_checked', false);
})->get();

// =====================================================
// 6. USING MODEL METHODS
// =====================================================

// Menggunakan method di Model BebasLaboratorium
$bebas = BebasLaboratorium::find($bebasLabId);

// Check status laboran complete
if ($bebas->isLaboranChecklistComplete()) {
    echo "Semua checklist laboran sudah diapprove";
}

// Check status kalab complete
if ($bebas->isKalabChecklistComplete()) {
    echo "Semua checklist kalab sudah diapprove";
}

// Get laboran dari laboratorium
$laboran = $bebas->laboran();

// Get semua checklists
$checklists = $bebas->checklists;

// =====================================================
// 7. QUERY YANG LEBIH COMPLEX
// =====================================================

// Ambil statistik bebas lab per laboratorium
$stats = BebasLaboratorium::select('laboratorium_id')
    ->selectRaw('COUNT(*) as total')
    ->selectRaw('SUM(CASE WHEN laboran_checked = 1 THEN 1 ELSE 0 END) as laboran_approved')
    ->selectRaw('SUM(CASE WHEN kalab_checked = 1 THEN 1 ELSE 0 END) as kalab_approved')
    ->groupBy('laboratorium_id')
    ->get();

// Ambil bebas lab dengan progress approval
$withProgress = BebasLaboratorium::with('checklists')
    ->get()
    ->map(function ($bebas) {
        $total = $bebas->checklists->count();
        $laboranApproved = $bebas->checklists->where('laboran_checked', 1)->count();
        $kalabApproved = $bebas->checklists->where('kalab_checked', 1)->count();

        return [
            'id' => $bebas->id,
            'laboratorium' => $bebas->laboratorium->nama_laboratorium,
            'laboran_progress' => ($total > 0) ? round(($laboranApproved / $total) * 100) : 0,
            'kalab_progress' => ($total > 0) ? round(($kalabApproved / $total) * 100) : 0,
        ];
    });

// =====================================================
// 8. RAW QUERIES (IF NEEDED)
// =====================================================

// Using DB facade jika perlu query kompleks
use Illuminate\Support\Facades\DB;

$result = DB::select('
    SELECT 
        bl.id,
        p.nama_pelanggan,
        lb.nama_laboratorium,
        COUNT(blc.id) as total_checklist,
        SUM(CASE WHEN blc.laboran_checked = 1 THEN 1 ELSE 0 END) as laboran_approved,
        SUM(CASE WHEN blc.kalab_checked = 1 THEN 1 ELSE 0 END) as kalab_approved
    FROM bebas_laboratorium bl
    JOIN pelanggans p ON bl.pelanggan_id = p.id
    JOIN laboratoriums lb ON bl.laboratorium_id = lb.id
    LEFT JOIN bebas_laboratorium_checklists blc ON bl.id = blc.bebas_laboratorium_id
    WHERE bl.pelanggan_id = ?
    GROUP BY bl.id
', [$pelangganId]);

// =====================================================
// 9. PAGINATION (FOR LIST VIEW)
// =====================================================

// Ambil bebas lab dengan pagination
$bebasLabs = BebasLaboratorium::where('pelanggan_id', $pelangganId)
    ->with(['laboratorium', 'checklists'])
    ->paginate(15);

// =====================================================
// 10. SEARCHING & FILTERING
// =====================================================

// Search berdasarkan nama laboratorium
$search = 'Biologi';
$results = BebasLaboratorium::whereHas('laboratorium', function ($q) use ($search) {
    $q->where('nama_laboratorium', 'LIKE', "%{$search}%");
})->get();

// Filter berdasarkan status approval
$status = 'pending'; // atau 'approved'
if ($status === 'pending') {
    $results = BebasLaboratorium::whereHas('checklists', function ($q) {
        $q->where('laboran_checked', false);
    })->get();
} else if ($status === 'approved') {
    $results = BebasLaboratorium::doesntHave('checklists', 'and', function ($q) {
        $q->where('laboran_checked', false);
    })->get();
}
