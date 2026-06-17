<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $fillable = ['resep_id', 'nilai'];

    public function resep()
    {
        return $this->belongsTo(Resep::class);
    }
}