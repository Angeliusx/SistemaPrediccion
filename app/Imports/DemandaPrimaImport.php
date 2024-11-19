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
use App\Models\User;
use App\Models\SecretarioJuzgado;
use App\Models\DescripcionJuzgado;
use App\Models\Deuda;
use App\Models\Estado;
use App\Models\UbiProceso;
use App\Models\DemandaPrimaDeuda;
use App\Models\Juzgado;
use App\Models\Demanda;
use App\Models\Proceso;
use App\Models\Afp;
use App\Models\EmpresaDato;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use DateTime;
use Carbon\Carbon;


class DemandaPrimaImport implements ToModel, WithHeadingRow
{
    private static $demandasNoImportadas = [];

    public function model(array $row)
    {

        if (!isset($row['ruc_empleador'])) {
            return null; // O manejar el caso en que no haya 'ruc_empleador'
        }

        $estudiosPermitidos = [100, 101, 103, 122];

        $IdsLetras = [
            'A' => "Ana Pacheco",
            'X' => "Ximena Zavaleta",
            'F' => "Fátima Rodríguez",
            'D' => "Deiner Diaz",
            'C' => "Cristhofer Fernández",
            'L' => "Lesslie Calle",
            'E' => "Edita Campos",
            'ED' => "Edgar Pacheco",
            'M' => "Milene Pacheco",
        ];

        $fecha_presentacion = null;

        $nr_demanda = (string)$row['nr_demanda'];
        $demandaprima = DemandaPrima::where('NR_DEMANDA', $nr_demanda)->first();
       
        if ($demandaprima) {
            if (isset($row['fe_emision'])) {
                $fe_emision = $row['fe_emision'];
                if (is_numeric($fe_emision)) {
                    $fe_emision_date = Date::excelToDateTimeObject($fe_emision);
                    $demandaprima->FE_EMISION = $fe_emision_date->format('Y-m-d');
                } else {
                    try {
                        $fe_emision_date = Carbon::parse($fe_emision);
                        $demandaprima->FE_EMISION = $fe_emision_date->format('Y-m-d');
                    } catch (\Exception $e) {
                        $demandaprima->FE_EMISION = null; // O algún valor predeterminado
                    }
                }
            }
            if (isset($row['cod_estudio'])) {
                $demandaprima->COD_ESTUDIO = $row['cod_estudio'];
            }
            if (isset($row['mto_total_demanda'])) {
                $demandaprima->MTO_TOTAL_DEMANDA = $row['mto_total_demanda'];
            }
            if (isset($row['tip_deuda'])) {
                $td_dividir = explode(",", $row['tip_deuda']);
                foreach ($td_dividir as $tip_deuda) {
                    DemandaPrimaDeuda::create([
                        'ID_DEMANDAP' => $demandaprima->ID_DEMANDAP,
                        'TIP_DEUDA' => Deuda::where('TIP_DEUDA', $tip_deuda)->value('TIP_DEUDA'),
                    ]);
                }
            }
            if (isset($row['codigo_unico_expediente'])) {
                $demandaprima->CODIGO_UNICO_EXPEDIENTE = $row['codigo_unico_expediente'];
            }
            if (isset($row['expediente'])) {
                $demandaprima->EXPEDIENTE = $row['expediente'];
            }
            if (isset($row['ano'])) {
                $demandaprima->AÑO = $row['ano'];
            }
            if (isset($row['estado'])) {
                $demandaprima->ID_ESTADO = Estado::where('ESTADO', $row['estado'])->value('ID_ESTADO');
            }
            if (isset($row['ubicacion_proceso'])) {
                $demandaprima->ID_UBIPROCESO = UbiProceso::where('UBIPROCESO', $row['ubicacion_proceso'])->value('ID_UBIPROCESO');
            }
            $juzgado = Juzgado::where('ID_JUZGADO', $demandaprima->ID_JUZGADO)->first();
            if ($juzgado == null || $juzgado->ID_JUZGADO == 1) {
                $secretario_juzgado_id = isset($row['secretario_juzgado']) ? 
                    SecretarioJuzgado::firstOrCreate(['SECRETARIO_JUZGADO' => $row['secretario_juzgado']])->ID_SJUZGADO : null;

                $descripcion_juzgado_id = isset($row['descripcion_juzgado']) ? 
                    DescripcionJuzgado::firstOrCreate(['DESCRIPCION_JUZGADO' => $row['descripcion_juzgado']])->ID_DJUZGADO : null;

                $codigo_juzgado = isset($row['codigo_juzgado']) ? $row['codigo_juzgado'] : null;
                $juzgado2 = Juzgado::create([
                    'CODIGO_JUZGADO' => $codigo_juzgado,
                    'ID_DJUZGADO' => $descripcion_juzgado_id,
                    'ID_SJUZGADO' => $secretario_juzgado_id,
                ]);
                $demandaprima->ID_JUZGADO = $juzgado2->ID_JUZGADO;
            } else {
                if (isset($row['secretario_juzgado'])) {
                    $juzgado->ID_SJUZGADO = SecretarioJuzgado::firstOrCreate(['SECRETARIO_JUZGADO' => $row['secretario_juzgado']])->ID_SJUZGADO; 
                }
                if (isset($row['descripcion_juzgado'])) {
                    $juzgado->ID_DJUZGADO = DescripcionJuzgado::firstOrCreate(['DESCRIPCION_JUZGADO' => $row['descripcion_juzgado']])->ID_DJUZGADO;
                }
                if (isset($row['codigo_juzgado'])) {
                    $juzgado->CODIGO_JUZGADO = $row['codigo_juzgado'];
                }
                $juzgado->save();
                $demandaprima->ID_JUZGADO = $juzgado->ID_JUZGADO;
            }
            $demandaprima->save();
            $cantidad = ['codigo_evento'];
            foreach ($cantidad as $codigo) {
                $indice = 1;
                while (isset($row[$codigo . '_' . $indice])) {
                    if (!empty($row['codigo_evento_' . $indice])) {
                        $codigo_evento = $row['codigo_evento_' . $indice];
                        $fecha_evento = $row['fecha_evento_' . $indice];
                        if (is_numeric($fecha_evento)) {
                            $fecha_evento = Date::excelToDateTimeObject($fecha_evento)->format('Y-m-d');
                        } else {
                            try {
                                $fecha_evento = Carbon::parse($fecha_evento)->format('Y-m-d');
                            } catch (\Exception $e) {
                                $fecha_evento = null;
                            }
                        }
                        $resolucion = $row['resolucion_' . $indice] ?? "0";
                        $observaciones = $row['observaciones_' . $indice] ?? null;
                        EventoDemandaPrima::updateOrCreate(
                            [
                                'ID_DEMANDAP' => $demandaprima->ID_DEMANDAP,
                                'CODIGO_EVENTO' => $codigo_evento,
                                'FECHA_EVENTO' => $fecha_evento,
                            ],
                            [
                                'RESOLUCION' => $resolucion,
                                'ID_REGISTRO' => 1,
                                'ID_UBIPROCESO' => $demandaprima->ID_UBIPROCESO,
                                'OBSERVACIONES' => $observaciones,
                            ]
                        );
                    }
                    $indice++;
                }
            }
        } else {
            try {
                if (isset($row['fe_emision'])) {
                    $fe_emision_date = $row['fe_emision'];

                    if (is_numeric($fe_emision_date)) {
                        $fe_emision = Date::excelToDateTimeObject($fe_emision_date);
                    } else {
                        try {
                            $fe_emision = Carbon::parse($fe_emision_date);
                        } catch (\Exception $e) {
                            $fe_emision = null; // O algún valor predeterminado
                        }
                    }
                }

                //Empresa RUC_EMPLEADOR

                if (isset($row['cod_estudio'])) {
                    $cod_estudio= Estudio::where('COD_ESTUDIO', $row['cod_estudio'])->value('COD_ESTUDIO');
                }else{
                    $cod_estudio = null;
                }

                if (isset($row['mto_total_demanda'])) {
                    $mto_total_demanda= $row['mto_total_demanda'];
                }else{
                    $mto_total_demanda = null;
                }

                if (isset($row['mto_deuda_actualizada'])) {
                    $mto_deuda_actualizada= $row['mto_deuda_actualizada'];
                }else{
                    $mto_deuda_actualizada = null;
                }

                if (isset($row['codigo_unico_expediente'])) {
                    $codigo_unico_expediente=(string)($row['codigo_unico_expediente']);
                }else{
                    $codigo_unico_expediente = null;
                }

                if (isset($row['fecha_presentacion'])) {
                    $fecha_presentacion_date = $row['fecha_presentacion'];

                    if (is_numeric($fe_emision_date)) {
                        $fecha_presentacion = Date::excelToDateTimeObject($fecha_presentacion_date);
                    } else {
                        try {
                            $fecha_presentacion = Carbon::parse($fecha_presentacion_date);
                        } catch (\Exception $e) {
                            $fecha_presentacion = null; // O algún valor predeterminado
                        }
                    }
                }

                if (isset($row['expediente'])) {
                    $expediente=$row['expediente'];
                }else{
                    $expediente = null;
                }

                if (isset($row['ano'])) {
                    $ano=$row['ano'];
                }else{
                    $ano = null;
                }
                                
                // Juzgado ID_JUZGADO

                $secretario_juzgado_id = isset($row['secretario_juzgado']) ? 
                    SecretarioJuzgado::firstOrCreate(['SECRETARIO_JUZGADO' => $row['secretario_juzgado']])->ID_SJUZGADO : null;
                $descripcion_juzgado_id = isset($row['descripcion_juzgado']) ? 
                    DescripcionJuzgado::firstOrCreate(['DESCRIPCION_JUZGADO' => $row['descripcion_juzgado']])->ID_DJUZGADO : null;
                $codigo_juzgado = isset($row['codigo_juzgado']) ? $row['codigo_juzgado'] : null;

                if (is_null($codigo_juzgado) && is_null($descripcion_juzgado_id) && is_null($secretario_juzgado_id)) {
                    $juzgado = Juzgado::where('ID_JUZGADO', 1)->first();
                } else {
                    $juzgado = Juzgado::updateOrCreate(
                        [
                            'CODIGO_JUZGADO' => $codigo_juzgado,
                            'ID_DJUZGADO' => $descripcion_juzgado_id,
                            'ID_SJUZGADO' => $secretario_juzgado_id,
                        ]
                    );
                }

                $estado = array_key_exists('estado', $row) ? ($row['estado'] === null ? 1 : (Estado::where('ESTADO', $row['estado'])->value('ID_ESTADO') ?? 1)) : 1;
                $ubiproceso = isset($row['ubicacion_proceso']) ? UbiProceso::where('UBIPROCESO', $row['ubicacion_proceso'])->value('ID_UBIPROCESO') : 2;
                $afp = 1;

                //Empresa RUC_EMPLEADOR

                $ruc_empleador = (string)$row['ruc_empleador'];
                $empresa = Empresa::where('RUC_EMPLEADOR', $ruc_empleador)->first();

                if(isset($row['tipo_empresa'])){
                    if ($row['tipo_empresa'] == 'PRI' || $row['tipo_empresa'] == 'PRIVADO' || $row['tipo_empresa'] == 'PRIVADA'|| $row['tipo_empresa'] == 'PRIVADAS' || $row['tipo_empresa'] == 'PRIV') {
                        $tipo_empresa = 1;
                    } elseif($row['tipo_empresa'] == 'PUB' || $row['tipo_empresa'] == 'PUBLICO' || $row['tipo_empresa'] == 'PUBLICA' || $row['tipo_empresa'] == 'PUBLICAS') {
                        $tipo_empresa = 2;
                    }
                }else{
                    $tipo_empresa = null;
                } 

                if (isset($row['distrito'])) {
                    $distrito = Distrito::where('DISTRITO', $row['distrito'])->value('ID_DIST');
                }else{
                    $distrito = null;
                }
    
                if (isset($row['provincia'])) {
                    $provincia = Provincia::where('PROVINCIA', $row['provincia'])->value('ID_P');
                }else{
                    $provincia = null;
                }
    
                if (isset($row['departamento'])) {
                    $departamento = Departamento::where('DEPARTAMENTO', $row['departamento'])->value('ID_D');
                }else{
                    $departamento = null;
                }

                if (isset($row['direcc'])) {
                    $direccion = (string) $row['direcc'];
                } else {
                    $direccion = null;
                }
    
                if (isset($row['locali'])) {
                    $locali = (string) $row['locali'];
                } else {
                    $locali = null;
                }
                
                if (isset($row['referencia'])) {
                    $referencia = (string) $row['referencia'];
                } else {
                    $referencia = null;
                }

                if (isset($row['razon_social'])) {
                    $razon_social = (string) $row['razon_social'];
                } else {
                    $razon_social = null;
                }

                $empresa = Empresa::UpdateOrCreate(
                    [
                    'RUC_EMPLEADOR' => $ruc_empleador,
                    ],
                    [
                    'RAZON_SOCIAL' => $razon_social,
                    'ID_TIPO' => $tipo_empresa,
                    'DIRECC' => $direccion,
                    'LOCALI' => $locali,
                    'REFERENCIA' => $referencia,
                    'DISTRITO' => $distrito,
                    'PROVINCIA' => $provincia,
                    'DEPARTAMENTO' => $departamento,
                ]);

                if (isset($row['telefono'])) {
                    $telefono_1 = (string)($row['telefono'] ? $row['telefono'] : null) ?? null;
                    $telefono_2 = str_replace(' ', '', $telefono_1);
                    if (strlen($telefono_2) <= 11) {
                        $telefono = $telefono_2; 
                    } else {
                        $telefono = null;
                    }
                } else {
                    $telefono = null;
                }

                $empresa_dato = EmpresaDato::updateOrCreate([
                    'RUC_EMPLEADOR' => $ruc_empleador,
                    'TELEFONO' =>  $telefono,
                ]);

                $demandaprima = new DemandaPrima([
                    'NR_DEMANDA' => $nr_demanda,
                    'FE_EMISION' => $fe_emision,
                    'RUC_EMPLEADOR' => $ruc_empleador,
                    'COD_ESTUDIO' => $cod_estudio,
                    'MTO_TOTAL_DEMANDA' => $mto_total_demanda,
                    'CODIGO_UNICO_EXPEDIENTE' => $codigo_unico_expediente,
                    'FECHA_PRESENTACION' => $fecha_presentacion,
                    'EXPEDIENTE' => $expediente,
                    'AÑO' => $ano,
                    'ID_JUZGADO' => $juzgado->ID_JUZGADO,
                ]);
                $demandaprima->save();

                if (isset($row['tip_deuda'])) {
                    $td_dividir = explode(",", $row['tip_deuda']);
                }else{
                    $td_dividir = null;
                }

                foreach ($td_dividir as $tip_deuda) {
                    $demandaprimadeuda= DemandaPrimaDeuda::Create([
                        'ID_DEMANDAP' => $demandaprima->ID_DEMANDAP,
                        'TIP_DEUDA' => Deuda::where('TIP_DEUDA', $tip_deuda)->value('TIP_DEUDA'),
                    ]);
                }

                if (isset($row['repro'])) {
                    $repro = $row['repro'];
                }else{
                    $repro = null;
                }
                
                $demanda = Demanda::Create([
                    'ID_DEMANDAP' => $demandaprima->ID_DEMANDAP,
                    'COD_AFP' => $afp,
                    'ID_ESTADO' => $estado,
                    'ID_UBIPROCESO' => $ubiproceso,
                    'REPRO' => $repro,
                    'MTO_DEUDA_ACTUALIZADA' => $mto_deuda_actualizada,
                ]);

                //Crear Eventos

                $cantidad = ['codigo_evento'];

                foreach ($cantidad as $codigo) {
                    $indice = 1;
                    while (isset($row[$codigo . '_' . $indice])) {
                        if (!empty($row['codigo_evento_' . $indice])) {
                            $fecha_evento = $row['fecha_evento_' . $indice];
                            if (is_numeric($fecha_evento)) {
                                $fecha_evento = Date::excelToDateTimeObject($fecha_evento)->format('Y-m-d');
                            } else {
                                try {
                                    $fecha_evento = Carbon::parse($fecha_evento)->format('Y-m-d');
                                } catch (\Exception $e) {
                                    $fecha_evento = null;
                                }
                            }
                            $eventodemandaprima = new EventoDemandaPrima([
                                'ID_DEMANDAP' => $demandaprima->ID_DEMANDAP,
                                'CODIGO_EVENTO' => $row['codigo_evento_' . $indice],
                                'RESOLUCION' => $row['resolucion_' . $indice] ?? "0",
                                'FECHA_EVENTO' => $fecha_evento,
                                'ID_REGISTRO' => 1,
                                'ID_UBIPROCESO' => $ubiproceso,
                                'OBSERVACIONES' => $row['observaciones_' . $indice] ?? null,
                            ]);

                            $eventodemandaprima->save();
                        }

                        if ($indice == 1 && is_null($fecha_presentacion)) {
                            $demandaprima->FECHA_PRESENTACION = $fecha_evento;
                            $demandaprima->save();
                        }
                        $indice++;
                    }
                }
            } 
            catch (\Exception $e) {
                self::$demandasNoImportadas[] = $row['nr_demanda'];
            }
        }
        
        return $demandaprima;
    }

    public static function getDemandasNoImportadas()
    {
        return self::$demandasNoImportadas;
    }
}

?>
