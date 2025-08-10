<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Momento extends Model
{
    protected $fillable = [
        'descricao',
    ];

    protected $appends = ['foto_url'];

    // Relacionamento: um momento tem muitos likes
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // Relacionamento: um momento tem muitas fotos
    public function fotos()
    {
        return $this->hasMany(Foto::class);
    }

    // Relacionamento: momento pertence a um usuário
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getFotoUrlAttribute()
    {
        if ($this->caminho_arquivo && file_exists(storage_path('app/public/' . $this->caminho_arquivo))) {
            return asset('s/' . $this->caminho_arquivo);
        }
    }
}
