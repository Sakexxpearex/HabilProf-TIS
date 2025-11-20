<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alumno extends Model
{
    use HasFactory;

    protected $table = 'alumno';
    protected $primaryKey = 'rut_alumno';
    public $incrementing = false;
    protected $keyType = 'integer';

    protected $fillable = [
        'rut_alumno',
        'nombre_alumno',
    ];

    public function habilitaciones()
    {
        return $this->hasMany(Habilitacion::class, 'rut_alumno', 'rut_alumno');
    }
}
