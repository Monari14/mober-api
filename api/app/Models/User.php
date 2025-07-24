<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Os atributos que podem ser atribuídos em massa.
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'avatar',
        'bio',
    ];

    /**
     * Atributos ocultos nos arrays e JSONs.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts para atributos do modelo.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Campos adicionais a serem incluídos no JSON automaticamente.
     */
    protected $appends = ['avatar_url'];

    /**
     * Relacionamento: um usuário tem muitos momentos.
     */
    public function momentos()
    {
        return $this->hasMany(Momento::class);
    }

    /**
     * Accessor para a URL do avatar do usuário.
     * Retorna o caminho da imagem enviada ou a imagem padrão.
     */
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar && file_exists(storage_path('app/public/' . $this->avatar))) {
            return asset('s/' . $this->avatar);
        }

        // Caminho para a imagem padrão (deve existir em public/images/avatar-default.png)
        return asset('i/avatar-default.png');
    }

    /**
     * Mutator para garantir que a senha seja sempre criptografada.
     */
    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = bcrypt($password);
    }
}
