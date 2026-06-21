<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'avatar'];
    protected $hidden = ['password', 'remember_token'];

    // avatar_url otomatis ikut di setiap response JSON user
    protected $appends = ['avatar_url'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function reseps()
    {
        return $this->hasMany(Resep::class);
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar ? url('/img/' . $this->avatar) : null;
    }
}