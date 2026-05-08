<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BebasLaboratoriumChecklist extends Model
{
    protected $table = 'bebas_laboratorium_checklists';

    protected $fillable = [
        'bebas_laboratorium_id',
        'checklist_number',
        'checklist_text',
        'laboran_checked',
        'laboran_checked_at',
        'kalab_checked',
        'kalab_checked_at',
    ];

    protected $casts = [
        'laboran_checked' => 'boolean',
        'kalab_checked' => 'boolean',
        'laboran_checked_at' => 'datetime',
        'kalab_checked_at' => 'datetime',
    ];

    /**
     * Relasi ke BebasLaboratorium
     */
    public function bebasLaboratorium()
    {
        return $this->belongsTo(BebasLaboratorium::class, 'bebas_laboratorium_id');
    }
}
