<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resep extends Model
{
    protected $fillable = ['user_id', 'nama', 'pembuat', 'waktu', 'kesulitan', 'kategori', 'video_url'];

    public function user()     { return $this->belongsTo(User::class); }
    public function gambars()  { return $this->hasMany(ResepGambar::class)->orderBy('urutan'); }
    public function bahans()   { return $this->hasMany(ResepBahan::class)->orderBy('urutan'); }
    public function langkahs() { return $this->hasMany(ResepLangkah::class)->orderBy('urutan'); }
    public function ulasans()  { return $this->hasMany(Ulasan::class)->latest(); }

    public function getAverageRatingAttribute(): float
    {
        return round($this->ulasans()->avg('nilai') ?? 0, 1);
    }
}