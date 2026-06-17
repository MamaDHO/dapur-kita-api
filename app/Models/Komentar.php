<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Komentar extends Model
{
    protected $fillable = ['resep_id', 'nama', 'isi'];

    public function resep()
    {
        return $this->belongsTo(Resep::class);
    }
}