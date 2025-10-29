<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Habilitacion extends Model
{
    use HasFactory;
    
    // Define todos los campos que pueden ser llenados masivamente desde el formulario
    protected $fillable = [
        'modalidad',
        'semestre_inicio',
        'alumno_id',
        'profesor_dinf_id',
        'titulo',
        'descripcion_proyecto',
        'co_guia_nombre',
        'comision_profesor_id',
        'empresa_nombre',
        'supervisor_empresa',
        'descripcion_practica',
        // Nota y fecha de nota se excluyen aquí porque se actualizan en otro momento
    ];

    // Define las relaciones para facilitar el Listado y Reportes
    public function alumno()
    {
        return $this->belongsTo(User::class, 'alumno_id');
    }

    public function profesorDinf()
    {
        return $this->belongsTo(User::class, 'profesor_dinf_id');
    }

    public function comisionProfesor()
    {
        return $this->belongsTo(User::class, 'comision_profesor_id');
    }
}