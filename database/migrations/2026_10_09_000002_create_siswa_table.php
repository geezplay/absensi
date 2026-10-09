<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->string('nis', 20);
            $table->string('nama', 100);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->unique(['kelas_id', 'nis']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswa');
    }
};
