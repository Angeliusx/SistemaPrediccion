<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prediccion extends Model
{
    use HasFactory;

    protected $table = 'predicciones';
    protected $primaryKey = 'ID_PREDICCION';
    public $timestamps = false; // 

    protected $fillable = [
        'ID_USUARIO',
        'PREDICCION',
        'FECHA_PREDICCION',
    ];


    public function usuario()
    {
        return $this->belongsTo(User::class, 'ID_USUARIO');
    }




}
