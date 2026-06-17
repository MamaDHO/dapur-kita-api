<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resep extends Model
{
    protected $fillable = ['nama', 'pembuat', 'waktu', 'kesulitan', 'kategori', 'video_url'];

    public function gambars()  { return $this->hasMany(ResepGambar::class)->orderBy('urutan'); }
    public function bahans()   { return $this->hasMany(ResepBahan::class)->orderBy('urutan'); }
    public function langkahs() { return $this->hasMany(ResepLangkah::class)->orderBy('urutan'); }
    public function ratings()  { return $this->hasMany(Rating::class); }
    public function komentars(){ return $this->hasMany(Komentar::class)->latest(); }

    public function getAverageRatingAttribute(): float
    {
        return round($this->ratings()->avg('nilai') ?? 0, 1);
    }
}