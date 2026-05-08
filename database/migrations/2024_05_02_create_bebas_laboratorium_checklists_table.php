<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBebasLaboratoriumChecklistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bebas_laboratorium_checklists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bebas_laboratorium_id');
            $table->integer('checklist_number');
            $table->text('checklist_text');
            $table->boolean('laboran_checked')->default(false);
            $table->timestamp('laboran_checked_at')->nullable();
            $table->boolean('kalab_checked')->default(false);
            $table->timestamp('kalab_checked_at')->nullable();
            $table->timestamps();

            $table->foreign('bebas_laboratorium_id')
                ->references('id')
                ->on('bebas_laboratorium')
                ->onDelete('cascade');

            $table->index('bebas_laboratorium_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bebas_laboratorium_checklists');
    }
}
