<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Periodo extends Model
{
    use HasFactory;

    protected $table = 'Periodo'; // Nombre de la tabla
    protected $primaryKey = 'ID_PERIODO'; // Llave primaria
    public $timestamps = false; // Si no usas las columnas `created_at` y `updated_at`

    protected $fillable = [
        'ID_PREJUDICIAL',
        'PERIODO_MES',
        'MONTO_PERIODO',
    ];

    // Relación con EmpresaPrejudicial
    public function empresaPrejudicial()
    {
        return $this->belongsTo(EmpresaPrejudicial::class, 'ID_PREJUDICIAL', 'ID_PREJUDICIAL');
    }
}
