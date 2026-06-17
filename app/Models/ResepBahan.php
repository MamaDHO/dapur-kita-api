<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ResepBahan extends Model
{
    protected $fillable = ['resep_id', 'isi', 'urutan'];

    // URL lengkap untuk dikonsumsi Flutter
    public function resep()
    {
        return $this->belongsTo(Resep::class);
    }
}