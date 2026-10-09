<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sesi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->string('mapel', 50);
            $table->date('tanggal');
            $table->string('jam_mulai', 5);
            $table->string('jam_selesai', 5);
            $table->string('topik', 150)->nullable();
            $table->string('status', 10)->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['kelas_id', 'tanggal', 'jam_mulai'], 'sesi_kelas_tanggal_jam_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sesi');
    }
};
