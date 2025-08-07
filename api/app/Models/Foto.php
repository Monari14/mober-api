<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Foto extends Model
{
    protected $fillable = [
        'momento_id',
        'caminho_arquivo',
    ];
    protected $appends = ['foto_url'];

    // Relacionamento: foto pertence a um momento
    public function momento()
    {
        return $this->belongsTo(Momento::class);
    }

    public function getFotoUrlAttribute()
    {
        if ($this->caminho_arquivo && file_exists(storage_path('app/public/' . $this->caminho_arquivo))) {
            return asset('s/' . $this->caminho_arquivo);
        }
    }
}
