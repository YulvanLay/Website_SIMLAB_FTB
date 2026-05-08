<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SetupBebasLaboratorium extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bebas:setup';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Setup Bebas Laboratorium tables dan sample data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting setup Bebas Laboratorium...');

        try {
            // Create bebas_laboratorium table
            if (!Schema::hasTable('bebas_laboratorium')) {
                $this->info('Creating bebas_laboratorium table...');
                DB::statement("
                    CREATE TABLE bebas_laboratorium (
                        id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                        kode_pelanggan INT NOT NULL,
                        laboratorium_id INT NOT NULL,
                        form_url VARCHAR(255) NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        
                        FOREIGN KEY (kode_pelanggan) REFERENCES pelanggans(kode_pelanggan) ON DELETE CASCADE,
                        FOREIGN KEY (laboratorium_id) REFERENCES laboratoriums(id) ON DELETE CASCADE,
                        INDEX idx_pelanggan (kode_pelanggan),
                        INDEX idx_laboratorium (laboratorium_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                $this->info('✓ bebas_laboratorium table created');
            } else {
                $this->info('✓ bebas_laboratorium table already exists');
            }

            // Create bebas_laboratorium_checklists table
            if (!Schema::hasTable('bebas_laboratorium_checklists')) {
                $this->info('Creating bebas_laboratorium_checklists table...');
                DB::statement("
                    CREATE TABLE bebas_laboratorium_checklists (
                        id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                        bebas_laboratorium_id BIGINT UNSIGNED NOT NULL,
                        checklist_number INT NOT NULL,
                        checklist_text VARCHAR(255) NOT NULL,
                        laboran_checked TINYINT(1) DEFAULT 0,
                        laboran_checked_at TIMESTAMP NULL,
                        kalab_checked TINYINT(1) DEFAULT 0,
                        kalab_checked_at TIMESTAMP NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        
                        FOREIGN KEY (bebas_laboratorium_id) REFERENCES bebas_laboratorium(id) ON DELETE CASCADE,
                        INDEX idx_bebas_lab (bebas_laboratorium_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
                $this->info('✓ bebas_laboratorium_checklists table created');
            } else {
                $this->info('✓ bebas_laboratorium_checklists table already exists');
            }

            // Insert sample data
            $this->info('Inserting sample data...');

            $count = DB::table('bebas_laboratorium')->count();
            if ($count == 0) {
                // Get first pelanggan
                $firstPelanggan = DB::table('pelanggans')->first();
                $kodePelanggan = $firstPelanggan->kode_pelanggan ?? 190023;

                // Get first 3 laboratoriums
                $laboratoriums = DB::table('laboratoriums')->limit(3)->get();

                foreach ($laboratoriums as $lab) {
                    DB::table('bebas_laboratorium')->insert([
                        'kode_pelanggan' => $kodePelanggan,
                        'laboratorium_id' => $lab->id,
                        'form_url' => 'https://form.kampus.ac.id/bebas-lab/lab-' . $lab->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $this->info('✓ Sample bebas_laboratorium data inserted');
            } else {
                $this->info('✓ bebas_laboratorium data already exists');
            }

            // Insert checklist items
            $checklistCount = DB::table('bebas_laboratorium_checklists')->count();
            if ($checklistCount == 0) {
                $bebasLabs = DB::table('bebas_laboratorium')->get();
                $checklists = [
                    'Bebas Pinjaman peralatan dan kunci loker',
                    'Sudah membersihkan biakan bakteri (Coldorom, CTR)',
                    'Sudah membayar bahan kimia dan media yang digunakan',
                    'Alat gelas dalam keadaan bersih dari label atau coret-coretan',
                    'Alat yang pecah atau rusak telah diganti',
                ];

                foreach ($bebasLabs as $bebas) {
                    foreach ($checklists as $idx => $text) {
                        DB::table('bebas_laboratorium_checklists')->insert([
                            'bebas_laboratorium_id' => $bebas->id,
                            'checklist_number' => $idx + 1,
                            'checklist_text' => $text,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                $this->info('✓ Sample checklist data inserted');
            } else {
                $this->info('✓ Checklist data already exists');
            }

            $this->info('');
            $this->line('<fg=green>✓ Setup completed successfully!</>');
            $this->line('You can now access: <fg=cyan>http://localhost/bebas-lab/</>');

            return 0;

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
