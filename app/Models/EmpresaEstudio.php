<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpresaEstudio extends Model
{
    protected $table = 'Empresas_Estudios';
    protected $primaryKey = 'RUC_EMPLEADOR';
    public $incrementing = false;
    public $timestamps = false; 

    protected $fillable = [
        'RUC_EMPLEADOR',
        'COD_ESTUDIO',
        'ID_EJECUTIVO',
        'ID_ESTADO',
        'COD_AFP',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'RUC_EMPLEADOR');
    }

    public function estudio()
    {
        return $this->belongsTo(Estudio::class, 'COD_ESTUDIO');
    }

    public function ejecutivo()
    {
        return $this->belongsTo(Ejecutivo::class, 'ID_EJECUTIVO');
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'ID_ESTADO');
    }

    public function afp()
    {
        return $this->belongsTo(Afp::class, 'COD_AFP');
    }
}
