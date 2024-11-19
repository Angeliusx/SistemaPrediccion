<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandaProfuturo extends Model
{
    protected $table = 'DemandasProfuturo';
    protected $primaryKey = 'ID_DEMANDAPRO';
    public $timestamps = false;

    protected $fillable = [
        'RUC_EMPLEADOR',
        'FE_EMISION',
        'COD_ESTUDIO',
        'TOTAL_DEMANDADO',
        'TIPO_DEUDA',
        'CODIGO_UNICO_EXPEDIENTE',
        'FECHA_PRESENTACION',
        'NRO_EXPEDIENTE',
        'AÑO',
        'ID_JUZGADO',
        'ID_ESTADO',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'RUC_EMPLEADOR', 'RUC_EMPLEADOR');
    }

    public function evento()
    {
        return $this->belongsToMany(Evento::class, 'Eventos_DemandasProfuturo', 'ID_DEMANDAPRO', 'CODIGO_EVENTO')
            ->withPivot('RESOLUCION')
            ->withPivot('FECHA_EVENTO')
            ->withPivot('ID_REGISTRO')
            ->withPivot('ID_UBICACION')
            ->withPivot('OBSERVACIONES')
            ->using(EventoDemandaProfuturo::class);
    }
    
    public function estudio()
    {
        return $this->belongsTo(Estudio::class, 'COD_ESTUDIO', 'COD_ESTUDIO');
    }

    public function deuda()
    {
        return $this->belongsTo(Deuda::class, 'TIPO_DEUDA', 'TIPO_DEUDA');
    }

    public function numeros()
    {
        return $this->hasMany(DemandaProfuturoNumero::class, 'ID_DEMANDAPRO');
    }

    public function juzgado()
    {
        return $this->belongsTo(Juzgado::class, 'ID_JUZGADO', 'ID_JUZGADO');
    }

}
