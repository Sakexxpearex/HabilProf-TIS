<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profesor extends Model
{
    use HasFactory;

    protected $table = 'profesor';
    protected $primaryKey = 'rut_profesor';
    public $incrementing = false;
    protected $keyType = 'integer';

    protected $fillable = [
        'rut_profesor',
        'nombre_profesor_departamento',
    ];

    public function habilitacionesComoGuia()
    {
        return $this->hasMany(Habilitacion::class, 'profesor_dinf_id', 'rut_profesor');
    }

    public function habilitacionesComoComision()
    {
        return $this->hasMany(Habilitacion::class, 'comision_profesor_id', 'rut_profesor');
    }
}
