<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'email', 'telefono', 'comprobante_residencia',
        'codigo_talento', 'resumen',
        'nivel_educacional', 'titulo_carrera', 'anio_egreso',
        'anios_experiencia', 'areas_experiencia',
        'competencias', 'rango_renta', 'tipo_jornada', 'modalidad',
        'cursos', 'idiomas', 'portafolio_url',
        'persona_discapacidad', 'validado', 'activo', 'porcentaje_completitud',
    ];

    public const CV_CIEGO_COLUMNS = [
        'id',
        'codigo_talento',
        'resumen',
        'nivel_educacional',
        'titulo_carrera',
        'anio_egreso',
        'anios_experiencia',
        'areas_experiencia',
        'competencias',
        'rango_renta',
        'tipo_jornada',
        'modalidad',
        'cursos',
        'idiomas',
        'portafolio_url',
        'persona_discapacidad',
    ];

    protected $casts = [
        'areas_experiencia'   => 'array',
        'competencias'        => 'array',
        'cursos'              => 'array',
        'idiomas'             => 'array',
        'persona_discapacidad'=> 'boolean',
        'validado'            => 'boolean',
        'activo'              => 'boolean',
    ];

    public function scopeSelectCvCiego(Builder $query): Builder
    {
        return $query->select(self::CV_CIEGO_COLUMNS);
    }

    public function getCvCiego(): array
    {
        return $this->only(self::CV_CIEGO_COLUMNS);
    }
}
