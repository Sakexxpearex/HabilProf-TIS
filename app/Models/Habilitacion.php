<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Habilitacion extends Model
{
    use HasFactory;

    protected $table = 'habilitacion';
    protected $primaryKey = 'id_habilitacion';

    protected $fillable = [
        'rut_alumno',
        'semestre_inicio',
        'descripcion',
        'nota_final',
        'fecha_nota',
        'semestre_inicio_anho',
        'tipo_habilitacion',
        'profesor_dinf_id',      // si corresponde
        'comision_profesor_id',  // si corresponde
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'rut_alumno', 'rut_alumno');
    }

    public function profesorDinf()
    {
        return $this->belongsTo(Profesor::class, 'profesor_dinf_id', 'rut_profesor');
    }

    public function comisionProfesor()
    {
        return $this->belongsTo(Profesor::class, 'comision_profesor_id', 'rut_profesor');
    }
}
