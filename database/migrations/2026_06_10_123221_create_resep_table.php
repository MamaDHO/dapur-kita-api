<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reseps', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('pembuat');
            $table->string('waktu');
            $table->enum('kesulitan', ['Mudah', 'Menengah', 'Sulit']);
            $table->enum('kategori', ['Sarapan', 'Makan Siang', 'Makan Malam', 'Cemilan']);
            $table->string('video_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resep');
    }
};
