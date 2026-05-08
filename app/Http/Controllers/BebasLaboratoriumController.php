<?php

namespace App\Http\Controllers;

use App\BebasLaboratorium;
use App\BebasLaboratoriumChecklist;
use App\Pelanggan;
use App\Laboratorium;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BebasLaboratoriumController extends Controller
{
    /**
     * Display view bebas laboratorium
     */
    public function index()
    {
        return view('bebas-lab.daftar');
    }

    public function laporan()
    {
        if (!auth()->user()->laboran && !auth()->user()->pelanggan && !auth()->user()->koordinator && !auth()->user()->kalab)
            return response()->view('errors.403');

        $pelanggans = Pelanggan::get();
        return view('bebas-lab.daftar', compact('pelanggans'));
    }


    public function getByPelanggan($kode_pelanggan)
    {
        $data = BebasLaboratorium::with([
            'pelanggan',
            'laboratorium',
            'laboran',
            'periode'
        ])
            ->where('kode_pelanggan', $kode_pelanggan)
            ->get();

        return response()->json([
            'data' => $data
        ]);

        // dd($data->toArray());
    }

    /**
     * Tampilkan halaman preview checklist
     */
    public function showPreview($id)
    {
        $bebas = BebasLaboratorium::with([
            'pelanggan',
            'laboratorium'
        ])->findOrFail($id);

        return view('bebas-lab.preview', compact('bebas'));
    }

    /**
     * Update checklist ke database bebas_laboratoriums
     */
    public function updateChecklist(Request $request)
    {
        try {
            $bebasId = $request->input('bebas_id');
            $checklistNumber = $request->input('checklist_number');
            $isChecked = $request->input('is_checked', false);
            $role = $request->input('role');

            \Log::info('Update checklist request', [
                'user' => auth()->user()->username ?? 'unknown',
                'bebas_id' => $bebasId,
                'checklist_number' => $checklistNumber,
                'is_checked' => $isChecked,
                'role' => $role,
            ]);

            // Find bebas laboratorium
            $bebas = BebasLaboratorium::findOrFail($bebasId);

            // Map checklist number ke kolom database
            $checklistColumns = [
                1 => 'ck_bebas_pinjaman',
                2 => 'ck_buka_bakteri',
                3 => 'ck_bayar_bahan',
                4 => 'ck_alat_bersih',
                5 => 'ck_alat_ganti',
            ];

            if (!isset($checklistColumns[$checklistNumber])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nomor checklist tidak valid',
                ], 422);
            }

            $column = $checklistColumns[$checklistNumber];

            // Update dengan mass assignment
            $bebas->update([
                $column => $isChecked ? 1 : 0,
            ]);

            \Log::info('Checklist updated successfully', [
                'bebas_id' => $bebasId,
                'column' => $column,
                'value' => $isChecked ? 1 : 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Checklist berhasil diperbarui',
                'data' => $bebas,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Bebas laboratorium not found', ['bebas_id' => $request->input('bebas_id')]);
            
            return response()->json([
                'success' => false,
                'message' => 'Data bebas laboratorium tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error updating checklist', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get detail bebas laboratorium
     */
    public function getDetail($id)
    {
        $bebas = BebasLaboratorium::with([
            'pelanggan',
            'laboratorium',
            'checklists'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $bebas,
        ]);
    }

    /**
     * Approve checklist oleh laboran
     */
    public function accLaboran(Request $request)
    {
        $bebasId = $request->input('bebas_id');
        
        $bebas = BebasLaboratorium::findOrFail($bebasId);

        // Cek apakah semua 5 checklist sudah di-check
        $columns = ['ck_bebas_pinjaman', 'ck_buka_bakteri', 'ck_bayar_bahan', 'ck_alat_bersih', 'ck_alat_ganti'];
        
        foreach ($columns as $column) {
            if (!$bebas->$column) {
                return response()->json([
                    'success' => false,
                    'message' => 'Semua syarat harus di-checklist terlebih dahulu',
                ], 422);
            }
        }

        // Update approval laboran
        $bebas->acc_laboran = 1;
        $bebas->save();

        return response()->json([
            'success' => true,
            'message' => 'Persetujuan laboran berhasil disimpan',
        ]);
    }

    /**
     * Approve checklist oleh kalab
     */
    public function accKalab(Request $request)
    {
        $bebasId = $request->input('bebas_id');
        
        $bebas = BebasLaboratorium::findOrFail($bebasId);

        // Cek apakah semua 5 checklist sudah di-check
        $columns = ['ck_bebas_pinjaman', 'ck_buka_bakteri', 'ck_bayar_bahan', 'ck_alat_bersih', 'ck_alat_ganti'];
        
        foreach ($columns as $column) {
            if (!$bebas->$column) {
                return response()->json([
                    'success' => false,
                    'message' => 'Semua syarat harus di-checklist terlebih dahulu',
                ], 422);
            }
        }

        // Update approval kalab
        $bebas->acc_kalab = 1;
        $bebas->save();

        return response()->json([
            'success' => true,
            'message' => 'Persetujuan kalab berhasil disimpan',
        ]);
    }

    // /**
    //  * Get data bebas laboratorium untuk datatable
    //  */
    // public function getData(Request $request)
    // {
    //     $kodePelanggan = $request->input('pelanggan_id');

    //     if (!$kodePelanggan) {
    //         return response()->json(['data' => []]);
    //     }

    //     $bebasLaboratoriums = BebasLaboratorium::where('kode_pelanggan', $kodePelanggan)
    //         ->with(['laboratorium', 'checklists'])
    //         ->get();

    //     $data = $bebasLaboratoriums->map(function ($bebas) {
    //         return [
    //             'id' => $bebas->id,
    //             'laboratorium_id' => $bebas->laboratorium_id,
    //             'laboratorium_name' => $bebas->laboratorium->nama_laboratorium ?? '-',
    //             'form_url' => $bebas->form_url ?? '#',
    //             'laboran_complete' => $bebas->isLaboranChecklistComplete(),
    //             'kalab_complete' => $bebas->isKalabChecklistComplete(),
    //             'checklists' => $bebas->checklists->toArray(),
    //         ];
    //     });

    //     return response()->json(['data' => $data]);
    // }

    // /**
    //  * Get checklist items untuk preview/review
    //  */
    // public function getChecklists($bebasLaboratoriumId)
    // {
    //     $bebas = BebasLaboratorium::with('checklists')->findOrFail($bebasLaboratoriumId);

    //     return response()->json([
    //         'success' => true,
    //         'data' => $bebas->checklists,
    //     ]);
    // }

    // /**
    //  * Update checklist laboran
    //  */
    // public function updateLaboranChecklist(Request $request)
    // {
    //     $checklistId = $request->input('checklist_id');
    //     $isChecked = $request->input('is_checked', false);

    //     $checklist = BebasLaboratoriumChecklist::findOrFail($checklistId);
    //     $checklist->laboran_checked = $isChecked;
    //     $checklist->laboran_checked_at = $isChecked ? Carbon::now() : null;
    //     $checklist->save();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Checklist laboran berhasil diperbarui',
    //         'data' => $checklist,
    //     ]);
    // }

    // /**
    //  * Update checklist kalab
    //  */
    // public function updateKalabChecklist(Request $request)
    // {
    //     $checklistId = $request->input('checklist_id');
    //     $isChecked = $request->input('is_checked', false);

    //     $checklist = BebasLaboratoriumChecklist::findOrFail($checklistId);
    //     $checklist->kalab_checked = $isChecked;
    //     $checklist->kalab_checked_at = $isChecked ? Carbon::now() : null;
    //     $checklist->save();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Checklist kalab berhasil diperbarui',
    //         'data' => $checklist,
    //     ]);
    // }

    // /**
    //  * Get pelanggan untuk select dropdown
    //  */
    // public function getPelangganList()
    // {
    //     $pelanggans = Pelanggan::select('id', 'nama_pelanggan')->get();

    //     return response()->json(['data' => $pelanggans]);
    // }
}
