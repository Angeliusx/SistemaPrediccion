<?php

namespace App\Exports;

use App\Models\Prediccion;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PrediccionesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        // Obtener todas las predicciones agrupadas por ID_USUARIO
        return Prediccion::with('usuario') // Relación con el modelo User
            ->select('ID_USUARIO')
            ->selectRaw('GROUP_CONCAT(PREDICCION SEPARATOR ", ") as predicciones') // Concatenar las predicciones en una sola celda
            ->groupBy('ID_USUARIO')
            ->get()
            ->map(function ($item) {
                // Mapear el resultado para incluir el nombre del usuario en lugar de ID
                return [
                    'usuario' => $item->usuario->name, // Mostrar el nombre del usuario
                    'predicciones' => $item->predicciones, // Predicciones concatenadas
                ];
            });
    }


    public function headings(): array
    {
        return [
            'ID Usuario',
            'Predicción',
        ];
    }
}
