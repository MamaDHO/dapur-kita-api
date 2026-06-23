<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ulasan extends Model
{
    protected $fillable = ['resep_id', 'user_id', 'nilai', 'isi'];

    public function resep()
    {
        return $this->belongsTo(Resep::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}