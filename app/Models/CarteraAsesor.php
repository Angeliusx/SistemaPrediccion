<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarteraAsesor extends Model
{
    use HasFactory;

    protected $table = 'Cartera_Asesor'; // Nombre de la tabla
    protected $primaryKey = 'ID_CARTERA'; // Llave primaria
    public $timestamps = false; // Si no usas las columnas `created_at` y `updated_at`

    protected $fillable = [
        'ASESOR',
        'TELEFONO',
        'CORREO_ASESOR',
        'NUMERO_CUENTA',
        'CCI_CUENTA',
    ];

    // Relación con EmpresaPrejudicial
    public function empresasPrejudiciales()
    {
        return $this->hasMany(EmpresaPrejudicial::class, 'ID_CARTERA', 'ID_CARTERA');
    }
}
