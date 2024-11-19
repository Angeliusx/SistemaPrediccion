<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandaProfuturoNumero extends Model
{
    protected $table = 'DemandasProfuturo_Numero';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'ID_DEMANDAPRO',
        'NUM_DEMANDA',
    ];

    public function demandaProfuturo()
    {
        return $this->belongsTo(DemandaProfuturo::class, 'ID_DEMANDAPRO', 'ID_DEMANDAPRO');
    }
}
