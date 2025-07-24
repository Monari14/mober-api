<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Foto extends Model
{
    protected $fillable = [
        'momento_id',
        'caminho_arquivo',
    ];

    // Relacionamento: foto pertence a um momento
    public function momento()
    {
        return $this->belongsTo(Momento::class);
    }
}
