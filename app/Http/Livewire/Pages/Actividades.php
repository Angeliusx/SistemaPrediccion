<?php
namespace App\Http\Livewire\Pages;

use App\Models\Actividad;
use Illuminate\Support\Facades\Http;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PrediccionesExport;
use App\Models\User;
use App\Models\Prediccion;

class Actividades extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $idUsuarioFiltro; // Agregamos una propiedad para almacenar el ID_USUARIO seleccionado
    public $busqueda = '';

    public function generarPredicciones()
    {
        
        $usuarios = User::all();

        foreach ($usuarios as $usuario) {
            // 2. Obtener el historial de actividades por mes del usuario
            $actividadesPorMes = Actividad::selectRaw('DATE_FORMAT(fecha_actividad, "%Y-%m") as mes, COUNT(*) as total')
                ->where('ID_USUARIO', $usuario->id)
                ->groupBy('mes')
                ->orderBy('mes', 'asc')
                ->get();

            // 3. Realizar la predicción (usando el enfoque de ML de antes, o un simple promedio)
            $registros = $actividadesPorMes->pluck('total')->toArray();
            $prediccion = $this->predecirSiguienteMes($registros);

            // 4. Guardar la predicción en la tabla predicciones
            Prediccion::create([
                'ID_USUARIO' => $usuario->id,
                'PREDICCION' => $prediccion,
                'FECHA_PREDICCION' => now(),
            ]);
        }

        return Excel::download(new PrediccionesExport, 'predicciones.xlsx');
    }

    public function predecirSiguienteMes($registros)
    {
        if (empty($registros)) {
            return 0;
        }

        $response = Http::post('http://127.0.0.1:5000/predict', [
            'registros' => $registros
        ]);

        if ($response->successful()) {
            $prediccion = $response->json()['prediccion'];
            return $prediccion;
        } else {
            return 0;
        }
    }
    

    
    public function render()
    {
        $query = Actividad::query();

        if ($this->idUsuarioFiltro) {
            $query->where('ID_USUARIO', $this->idUsuarioFiltro);
        }

        $query->where('ACTIVIDAD', 'like', "%{$this->busqueda}%");


        $actividades = $query->orderBy('ID_ACTIVIDAD', 'desc')->paginate(10);

        $opcionesID_USUARIO = User::all(); // Obtener opciones para el select de ID_USUARIO

        return view('livewire.pages.actividades', [
            'actividades' => $actividades,
            'opcionesID_USUARIO' => $opcionesID_USUARIO,
        ]);
    }
}
