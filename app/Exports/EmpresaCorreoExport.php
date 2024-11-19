<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\DemandaPrimaDeuda;
use App\Models\DemandaPrima;
use App\Models\Evento;
use App\Models\Deuda;
use App\Models\Empresa;
use App\Models\EmpresaEstudio;
use App\Models\RegistroCorreo;
use App\Models\Estudio;
use App\Models\Distrito;
use App\Models\Provincia;
use App\Models\Departamento;
use App\Models\EventoDemandaPrima;
use Illuminate\Support\Collection;

class EmpresaCorreoExport implements FromCollection, WithHeadings
{
    private $data;
    private $mes; // Nueva propiedad para el mes

    public function __construct(Collection $data, $mes = null) // Acepta el mes como parámetro
    {
        $this->data = $data;
        $this->mes = $mes; // Inicializa la propiedad mes
    }


    public function collection()
    {
        return $this->data->map(function ($empresa) {
            // Inicializa la consulta de registros
            $query = RegistroCorreo::where('RUC_EMPLEADOR', (string)$empresa->RUC_EMPLEADOR);
            
            // Filtrar por mes solo si se seleccionó un mes
            if (!empty($this->mes)) {
                $query->whereMonth('FECHA', $this->mes); // Filtrar por mes
            }
            
            // Obtener los registros
            $registros = $query->orderBy('FECHA', 'desc')->get();
    
            $registrosUnique = $registros->unique('FECHA');
    
            // Aplanar los registros
            $registrosFlattened = $registrosUnique->flatMap(function ($registro) {
                return array_values([
                    'TIPO_CORREO'=>$registro->TIPO_CORREO,
                    'FECHA' => \Carbon\Carbon::parse($registro->FECHA)->format('d/m/Y'),
                ]);
            })->toArray();
            
            $cod = EmpresaEstudio::where('RUC_EMPLEADOR', (string)$empresa->RUC_EMPLEADOR)->first();
            
            $empresaData = [
                'RUC_EMPLEADOR' => $empresa->RUC_EMPLEADOR,
                'RAZON_SOCIAL' => $empresa->RAZON_SOCIAL,
                'DEPARTAMENTO' => $empresa->departamento ? $empresa->departamento->DEPARTAMENTO : '',
                'CODIGO_ESTUDIO' => $cod ? $cod->COD_ESTUDIO : '',
            ];
    
            return array_merge($empresaData, $registrosFlattened);
        });
    }


    public function headings(): array
    {
        return [
            'RUC_EMPLEADOR',
            'RAZON_SOCIAL',
            'DEPARTAMENTO',
            'CODIGO_ESTUDIO',
            'TIPO_CORREO',
            'FECHA',
        ];
    }
}
