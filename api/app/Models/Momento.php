<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Momento extends Model
{
    protected $fillable = [
        'titulo',
        'descricao',
        'data',
        'sentimento',
        'local',
        'visibilidade',
    ];

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
}
