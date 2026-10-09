<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_guru_id')->constrained('users')->cascadeOnDelete();
            $table->string('nama', 50);
            $table->string('tingkat', 10);
            $table->string('tahun_ajaran', 20);
            $table->string('mapel', 50);
            $table->string('wali_kelas')->nullable();
            $table->string('keterangan')->nullable();
            $table->string('status', 10)->default('aktif');
            $table->timestamps();

            $table->unique(['owner_guru_id', 'nama', 'mapel']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
