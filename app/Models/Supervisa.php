<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supervisa extends Model
{
    protected $table = 'supervisa';

    protected $fillable = [
        'tipo_supervision',
        'profesor_id',
        'alumno_id',
        'habilitacion_id'
    ];

    public function alumno() {
        return $this->belongsTo(Alumno::class);
    }

    public function profesor() {
        return $this->belongsTo(Profesor::class);
    }

    public function habilitacion() {
        return $this->belongsTo(Habilitacion::class);
    }
}
