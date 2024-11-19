<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpresaPrejudicial extends Model
{
    use HasFactory;

    protected $table = 'Empresa_Prejudicial'; // Nombre de la tabla
    protected $primaryKey = 'ID_PREJUDICIAL'; // Llave primaria
    public $timestamps = false; // Si no usas las columnas `created_at` y `updated_at`

    protected $fillable = [
        'RUC_EMPLEADOR',
        'TOTAL_GENERAL',
        'GASTOS',
        'ID_CARTERA',
    ];

    // Relación con CarteraAsesor
    public function carteraAsesor()
    {
        return $this->belongsTo(CarteraAsesor::class, 'ID_CARTERA', 'ID_CARTERA');
    }

    // Relación con Periodo
    public function periodos()
    {
        return $this->hasMany(Periodo::class, 'ID_PREJUDICIAL', 'ID_PREJUDICIAL');
    }

    // Relación con Empresa
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'RUC_EMPLEADOR', 'RUC_EMPLEADOR');
    }
}
