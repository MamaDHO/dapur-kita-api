<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ResepGambar extends Model
{
    protected $fillable = ['resep_id', 'path', 'urutan'];

    public function resep()
    {
        return $this->belongsTo(Resep::class);
    }
    // URL lengkap untuk dikonsumsi Flutter
    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }
}