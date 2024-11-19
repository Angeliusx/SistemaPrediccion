<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\DemandaPrima;
use App\Models\Empresa;
use App\Models\Estudio;
use App\Models\Evento;
use App\Models\Distrito;
use App\Models\Provincia;
use App\Models\Departamento;
use App\Models\Actividad;
use App\Models\EventoDemandaPrima;
use App\Models\EmpresaDato;
use App\Models\EmpresaRpL;
use App\Models\EmpresaEstudio;
use App\Models\Periodo;
use App\Models\EmpresaPrejudicial;
use App\Models\CarteraAsesor;
use App\Models\User;
use DateTime;



class EmpresaPreJudicialImport implements ToModel, WithHeadingRow
{
    private static $NoImportadas = [];

    public function model(array $row)
    {
        if (isset($row['ruc_empleador'])) {
            $ruc = (string)$row['ruc_empleador'];
            $empresa = Empresa::where('RUC_EMPLEADOR', $ruc)->first();
                try{ 
                    $empresa = Empresa::where('RUC_EMPLEADOR', $ruc)->first();
                    
                    if($empresa){
                        
                        $prejudicial = EmpresaPrejudicial::where('RUC_EMPLEADOR', $ruc)->first();
    
                        if($prejudicial){
                            if (isset($row['total_general'])) {
                                $prejudicial->TOTAL_GENERAL = $row['total_general'];
                            }
                            if (isset($row['gastos'])) {
                                $prejudicial->GASTOS = $row['gastos'];
                            }
                            if (isset($row['telefono'])) {
                                $asesor = CarteraAsesor::where('TELEFONO', $row['telefono'])->first();
                                if ($asesor) {
                                    $prejudicial->ID_CARTERA = $asesor->ID_CARTERA;
                                }
                            }
                            $prejudicial->save();
                            
                            if ($empresa->FECHA_CARGA == null){
                                $empresa->FECHA_CARGA = (new DateTime('now'))->format('Y-m-d');
                            }
                            else {
                                $empresa->FECHA_CARGA = (new DateTime('now'))->format('Y-m-d');
                            }
                            $empresa->save();
                            
                            Periodo::where('ID_PREJUDICIAL', $prejudicial->ID_PREJUDICIAL)->delete();
                        } else {
                            if ($empresa->FECHA_CARGA == null){
                                $empresa->FECHA_CARGA = (new DateTime('now'))->format('Y-m-d');
                            }
                            else {
                                $empresa->FECHA_CARGA = (new DateTime('now'))->format('Y-m-d');
                            }
                            $empresa->save();
                    
                            $prejudicial = new EmpresaPrejudicial([
                                'RUC_EMPLEADOR' => $ruc,
                                'TOTAL_GENERAL' => $row['total_general'] ?? 0,
                                'GASTOS' => $row['gastos'] ?? 0,
                                'ID_CARTERA' => null,
                            ]);
                    
                            if (isset($row['telefono'])) {
                                $asesor = CarteraAsesor::where('TELEFONO', $row['telefono'])->first();
                                if ($asesor) {
                                    $prejudicial->ID_CARTERA = $asesor->ID_CARTERA;
                                }
                            }
                    
                            $prejudicial->save();
                        }
        
                        $columnasPeriodo = array_filter(array_keys($row), function($columna) {
                            return strpos($columna, 'periodo_') === 0;
                        });
                        
                        foreach ($columnasPeriodo as $columna) {
                            $periodoMes = substr($columna, 8);
                            $montoPeriodo = $row[$columna];
                            
                        
                            if (!empty($montoPeriodo)) {
                                $prejudicial = EmpresaPrejudicial::where('RUC_EMPLEADOR', $ruc)->first();
                        
                                if (!empty($montoPeriodo)) {
                                    $periodo = new Periodo([
                                        'ID_PREJUDICIAL' => $prejudicial->ID_PREJUDICIAL,
                                        'PERIODO_MES' => $periodoMes,
                                        'MONTO_PERIODO' => $montoPeriodo,
                                    ]);
        
                                    $periodo->save();
                                }
                            }
                        }
                    }
                    else{
                       self::$NoImportadas[] = $ruc; 
                    }
                }catch(\Exception $e){
                    self::$NoImportadas[] = $ruc;
                }
        } else {
            
        }
    }

    public static function getNoImportadas()
    {
        return self::$NoImportadas;
    }
}
?>
