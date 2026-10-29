<?php

declare(strict_types=1);

namespace App\Models; 

use Illuminate\Database\Eloquent\Model;

class CursosMatricula extends Model
{
    protected $table = 'cursos';

    public $timestamps = false;

    protected $fillable = [
        'codigo_curso',
        'nombre',
        'creditos',
        'cupo_maximo',
        'estado',
    ];

    protected $casts = [
        'id' => 'integer',
        'creditos' => 'integer',
        'cupo_maximo' => 'integer',
        'estado' => 'boolean',
    ];
}
