<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // Sangat penting agar responden & admin bisa dibedakan
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the usaha associated with the user.
     * Relasi ini memungkinkan kita memanggil: $user->usaha
     */
    public function usaha()
    {
        return $this->hasOne(Usaha::class);
    }

    /**
     * Check if user is admin
     * Digunakan untuk membatasi hak akses di backend
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is responden
     * Digunakan saat login/register untuk memastikan mereka pendaftar dari Flutter
     */
    public function isResponden()
    {
        return $this->role === 'responden';
    }
}
