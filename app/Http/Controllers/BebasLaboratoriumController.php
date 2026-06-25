<?php

namespace App\Http\Controllers;

use App\BebasLaboratorium;
use App\BebasLaboratoriumChecklist;
use App\Laboran;
use App\Pelanggan;
use App\Laboratorium;
use App\Pejabat;
use PDF;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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


        return
            response()->json([
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
            $isChecked = filter_var(
                $request->input('is_checked'),
                FILTER_VALIDATE_BOOLEAN
            );
            $role = $request->input('role');

            Log::info('Update checklist request', [
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
                $column => $isChecked
            ]);

            Log::info('Checklist updated successfully', [
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
            Log::error('Bebas laboratorium not found', ['bebas_id' => $request->input('bebas_id')]);

            return response()->json([
                'success' => false,
                'message' => 'Data bebas laboratorium tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error updating checklist', [
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
        $kodeLaboran = $request->input('kode_laboran');

        $bebas = BebasLaboratorium::findOrFail($bebasId);
        $kodeKalab = Laboratorium::where("id", $bebas->laboratorium_id)->value("kode_pejabat");

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
        $bebas->acc_laboran = $kodeLaboran;
        $bebas->acc_kalab = $kodeKalab;
        $bebas->tanggal_acc_kalab = now();
        $bebas->save();

        $namaqr = $bebas->kode_pelanggan . "-" . $bebas->laboratorium->nama_laboratorium;

        QrCode::size(300)
            ->format('png')
            ->generate(config('app.url') . '/form-bebas-lab/' . $bebas->kode_pelanggan . '/' . str_replace(' ', '-', $bebas->laboratorium->nama_laboratorium), public_path('qrcode/' . $namaqr . '.png'));

        $this->sendEmail($bebas);

        return response()->json([
            'success' => true,
            'message' => 'Persetujuan laboran berhasil disimpan',
        ]);
    }

    public function batalAccLaboran(Request $request)
    {
        $bebasId = $request->input('bebas_id');

        $bebas = BebasLaboratorium::findOrFail($bebasId);

        // Update batal approval laboran
        $bebas->acc_laboran = 0;
        $bebas->acc_kalab = 0;
        $bebas->save();

        return response()->json([
            'success' => true,
            'message' => 'Batal persetujuan laboran berhasil disimpan',
        ]);
    }

    public function sendEmail(BebasLaboratorium $bebas)
    {
        //ambil data-data yang diperlukan untuk kirim email
        $pelanggan = Pelanggan::where('kode_pelanggan', $bebas->kode_pelanggan)->firstOrFail();
        $laboran = Laboran::where('kode_laboran', $bebas->acc_laboran)->firstOrFail();
        $kalab = Pejabat::where('kode_pejabat', $bebas->acc_kalab)->firstOrFail();
        $laboratorium = Laboratorium::find($bebas->laboratorium_id);
        $waktu = Carbon::now()->toTimeString();
        $tanggal = Carbon::parse($bebas->tanggal_acc_kalab)
            ->isoFormat('DD MMMM YYYY');
        $tahun = Carbon::parse($bebas->tanggal_acc_kalab)->year;
        $namaEdit = Laboratorium::where("id", $bebas->laboratorium_id)->value('nama_laboratorium');


        $subject = 'Notifikasi Persetujuan Bebas Laboratorium';

        $data = [
            'bebasLaboratorium' => $bebas,
            'pelanggan' => $pelanggan,
            'laboratorium' => $laboratorium,
            'kalab' => $kalab,
            'tanggal' => $tanggal,
            'waktu' => $waktu,
        ];


        $daftarChecklist = [
            'ck_bebas_pinjaman' => 'Peralatan gelas dan kunci loker',
            'ck_buka_bakteri' => 'Membersihkan biakan bakteri (Coldroom, CTR)',
            'ck_bayar_bahan' => 'Membayar bahan dan media yang digunakan',
            'ck_alat_bersih' => 'Alat gelas dalam keadaan bersih dari label atau coretan-coretan',
            'ck_alat_ganti' => 'Alat yang pecah atau rusak suddah diganti'
        ];

        $pdf = PDF::loadView('laporan.form-bebas-lab', compact('namaEdit', 'pelanggan', 'laboran', 'kalab', 'bebas', 'daftarChecklist', 'tanggal', 'tahun'));

        $to_pelanggan_email = 's160423187@student.ubaya.ac.id';
        $to_pealnggan_name = 'pelanggan';

        $to_kalab_email = 'piser647@gmail.com';
        $to_kalab_name = 'kalab';

        $to_laboran_email = 'pesir8776@gmail.com';
        $to_laboran_name = 'laboran';

        Mail::send('bebas-lab.mail-acc-kalab', $data, function ($message) use ($subject, $pdf, $laboratorium, $to_laboran_email, $to_laboran_name, $to_pelanggan_email, $to_pealnggan_name) {

            $message->to(
                $to_pelanggan_email,
                $to_pealnggan_name
            )->cc(
                    $to_laboran_email,
                    $to_laboran_name
                )
                ->subject($subject)
                ->attachData(
                    $pdf->output(),
                    'Bebas Lab - ' . $laboratorium->nama_laboratorium . '.pdf'
                );

            $message->setReplyTo(
                'julvanlay789@gmail.com',
                'Simlab FTB'
            );
        });

        Mail::send('bebas-lab.mailToKalab', $data, function ($message) use ($to_kalab_name, $to_kalab_email, $subject) {

            $message->to($to_kalab_email, $to_kalab_name)
                ->subject($subject);
            $message->setReplyTo('julvanlay789@gmail.com', 'Simlab FTB');
        });



    }

    //pakai jika tombol acc kalab mau digunakan

    // public function accKalab(Request $request)
    // {
    //     $bebasId = $request->input('bebas_id');
    //     $kodeKalab = $request->input('kode_kalab');
    //     $kodePelanggan = $request->input('kode_pelanggan');

    //     $bebas = BebasLaboratorium::findOrFail($bebasId);

    //     //Cek apakah laboran sudah memberikan acc
    //     if ($bebas->acc_laboran != 0) {
    //         $bebas->acc_kalab = $kodeKalab;
    //         $bebas->tanggal_acc_kalab = now();
    //         $bebas->save();

    //         $this->emailAccKalab($bebasId);

    //         $namaqr = $kodePelanggan . "-" . $bebas->laboratorium->nama_laboratorium;

    //         QrCode::size(300)
    //             ->format('png')
    //             ->generate(config('app.url') . '/form-bebas-lab/' . $kodePelanggan . '/' . str_replace(' ', '-', $bebas->laboratorium->nama_laboratorium), public_path('qrcode/' . $namaqr . '.png'));

    //         return response()->json([
    //             'status' => 'oke',
    //             'message' => 'Persetujuan kalab berhasil disimpan',
    //         ]);
    //     } else {
    //         return response()->json([
    //             'status' => 'gagal',
    //             'message' => 'Gagal memberikan persetujuan',
    //         ], 422);
    //     }

    // }

    public function cetak($kodePelanggan, $namaLab)
    {
        $namaEdit = str_replace('-', ' ', $namaLab);
        $idLab = Laboratorium::where('nama_laboratorium', $namaEdit)->firstOrFail()->id;
        $bebas = BebasLaboratorium::with([
            'pelanggan',
            'laboratorium',
        ])
            ->where('kode_pelanggan', $kodePelanggan)
            ->where('laboratorium_id', $idLab)
            ->firstOrFail();

        $pelanggan = Pelanggan::where('kode_pelanggan', $kodePelanggan)->firstOrFail();
        $laboran = Laboran::where('kode_laboran', $bebas->acc_laboran)->firstOrFail();
        $kalab = Pejabat::where('kode_pejabat', $bebas->acc_kalab)->firstOrFail();
        $tanggal = Carbon::parse($bebas->tanggal_acc_kalab)
            ->isoFormat('DD MMMM YYYY');
        $tahun = Carbon::parse($bebas->tanggal_acc_kalab)->year;

        $daftarChecklist = [
            'ck_bebas_pinjaman' => 'Peralatan gelas dan kunci loker',
            'ck_buka_bakteri' => 'Membersihkan biakan bakteri (Coldroom, CTR)',
            'ck_bayar_bahan' => 'Membayar bahan dan media yang digunakan',
            'ck_alat_bersih' => 'Alat gelas dalam keadaan bersih dari label atau coretan-coretan',
            'ck_alat_ganti' => 'Alat yang pecah atau rusak suddah diganti'
        ];


        $pdf = PDF::loadView('laporan.form-bebas-lab', compact('namaEdit', 'pelanggan', 'laboran', 'kalab', 'bebas', 'daftarChecklist', 'tanggal', 'tahun'));
        $pdf->setPaper('A4', 'potrait');
        $pdf->getDomPDF()->set_option("enable_php", true);


        return $pdf->stream("Form Bebas Lab - " . $pelanggan->nama_pelanggan . ".pdf");
    }

    // public function emailToKalab($bebasLabId)
    // {
    //     $bebasLaboratorium = BebasLaboratorium::find($bebasLabId);
    //     $pelanggan = Pelanggan::find($bebasLaboratorium->kode_pelanggan);
    //     $laboratorium = Laboratorium::find($bebasLaboratorium->laboratorium_id);
    //     $kalab = Pejabat::find($laboratorium->kode_pejabat);
    //     $tanggal = Carbon::now()->isoFormat('DD MMMM YYYY');
    //     $waktu = Carbon::now()->toTimeString();

    //     $to_name = "ubaya";
    //     $to_email = "s160423189@student.ubaya.ac.id";
    //     $from_email = "julvanlay789@gmail.com";
    //     $from_name = "Yulfan";

    //     $subject = 'Notifikasi Persetujuan Bebas Laboratorium';

    //     $data = [
    //         'bebasLaboratorium' => $bebasLaboratorium,
    //         'pelanggan' => $pelanggan,
    //         'laboratorium' => $laboratorium,
    //         'kalab' => $kalab,
    //         'tanggal' => $tanggal,
    //         'waktu' => $waktu,
    //     ];

    //     Mail::send('bebas-lab.mailToKalab', $data, function ($message) use ($to_name, $to_email, $subject, $from_email, $from_name) {

    //         $message->to($to_email, $to_name)
    //             ->subject($subject);
    //         $message->setReplyTo('sistem@simlabftb.top', 'Simlab FTB');
    //     });
    // }

    // public function emailAccKalab($bebasLabId)
    // {
    //     $bebas = BebasLaboratorium::find($bebasLabId);
    //     $pelanggan = Pelanggan::find($bebas->kode_pelanggan);
    //     $laboratorium = Laboratorium::find($bebas->laboratorium_id);
    //     $kalab = Pejabat::find($laboratorium->kode_pejabat);
    //     $laboran = Laboran::where('kode_laboran', $bebas->acc_laboran)->firstOrFail();
    //     $tanggal = Carbon::now()->isoFormat('DD MMMM YYYY');
    //     $waktu = Carbon::now()->toTimeString();
    //     $tanggal = Carbon::parse($bebas->tanggal_acc_kalab)
    //         ->isoFormat('DD MMMM YYYY');
    //     $tahun = Carbon::parse($bebas->tanggal_acc_kalab)->year;

    //     $to_name = "ubaya";
    //     $to_email = "s160423187@student.ubaya.ac.id";

    //     $subject = 'Notifikasi ACC Bebas Laboratorium';
    //     $data = [
    //         'bebasLaboratorium' => $bebas,
    //         'pelanggan' => $pelanggan,
    //         'laboratorium' => $laboratorium,
    //         'kalab' => $kalab,
    //         'tanggal' => $tanggal,
    //         'waktu' => $waktu,
    //     ];

    //     $namaEdit = $laboratorium->nama_laboratorium;

    //     $daftarChecklist = [
    //         'ck_bebas_pinjaman' => 'Peralatan gelas dan kunci loker',
    //         'ck_buka_bakteri' => 'Membersihkan biakan bakteri (Coldroom, CTR)',
    //         'ck_bayar_bahan' => 'Membayar bahan dan media yang digunakan',
    //         'ck_alat_bersih' => 'Alat gelas dalam keadaan bersih dari label atau coretan-coretan',
    //         'ck_alat_ganti' => 'Alat yang pecah atau rusak suddah diganti'
    //     ];
    //     $pdf = PDF::loadView('laporan.form-bebas-lab', compact('namaEdit', 'pelanggan', 'laboran', 'kalab', 'bebas', 'daftarChecklist', 'tanggal', 'tahun'));

    //     Mail::send('bebas-lab.mail-acc-kalab', $data, function ($message) use ($pelanggan, $subject, $pdf, $laboratorium, $to_email, $to_name) {

    //         $message->to(
    //             $to_email,
    //             $to_name
    //         )
    //             ->subject($subject)
    //             ->attachData(
    //                 $pdf->output(),
    //                 'Bebas Lab - ' . $laboratorium->nama_laboratorium . '.pdf'
    //             );

    //         $message->setReplyTo(
    //             'sistem@simlabftb.top',
    //             'Simlab FTB'
    //         );
    //     });

    // }


}
