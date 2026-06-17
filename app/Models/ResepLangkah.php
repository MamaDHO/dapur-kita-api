<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResepLangkah extends Model
{
    protected $fillable = ['resep_id', 'isi', 'urutan'];

    public function resep()
    {
        return $this->belongsTo(Resep::class);
    }
}