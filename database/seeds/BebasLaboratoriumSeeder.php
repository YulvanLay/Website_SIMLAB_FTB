<?php

use Illuminate\Database\Seeder;
use App\BebasLaboratorium;
use App\BebasLaboratoriumChecklist;

class BebasLaboratoriumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $checklistTexts = [
            1 => 'Bebas Pinjaman peralatan dan kunci loker',
            2 => 'Sudah membersihkan biakan bakteri (Coldorom, CTR)',
            3 => 'Sudah membayar bahan kimia dan media yang digunakan',
            4 => 'Alat gelas dalam keadaan bersih dari label atau coret-coretan',
            5 => 'Alat yang pecah atau rusak telah diganti',
        ];

        // Contoh: Buat bebas laboratorium records dengan checklists
        // Ini hanya untuk development purposes, bisa disesuaikan sesuai kebutuhan

        // Jika ingin auto-create bebas lab records, uncomment di bawah ini:
        /*
        $bebasLabs = BebasLaboratorium::all();

        foreach ($bebasLabs as $bebasLab) {
            // Check apakah sudah ada checklists
            if ($bebasLab->checklists()->count() == 0) {
                foreach ($checklistTexts as $number => $text) {
                    BebasLaboratoriumChecklist::create([
                        'bebas_laboratorium_id' => $bebasLab->id,
                        'checklist_number' => $number,
                        'checklist_text' => $text,
                    ]);
                }
            }
        }
        */
    }
}
