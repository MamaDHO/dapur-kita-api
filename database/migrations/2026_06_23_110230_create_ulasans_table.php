<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ulasans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resep_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('nilai'); // 1-5
            $table->text('isi');
            $table->timestamps();

            // Satu user hanya bisa punya satu ulasan per resep
            $table->unique(['resep_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ulasans');
    }
};