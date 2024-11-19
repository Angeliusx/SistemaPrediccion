<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\DemandaProfuturo;
use App\Models\Empresa;
use App\Models\EmpresaDato;
use App\Models\Evento;
use App\Models\Deuda;
use App\Models\Distrito;
use App\Models\Provincia;
use App\Models\SecretarioJuzgado;
use App\Models\DescripcionJuzgado;
use App\Models\Departamento;
use App\Models\DemandaProfuturoNumero;
use App\Models\Juzgado;
use App\Models\Demanda;
use App\Models\Proceso;
use App\Models\Afp;
use App\Models\EventoDemandaProfuturo;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use DateTime;
use Carbon\Carbon;



class DemandaProfuturoImport implements ToModel, WithHeadingRow
{
    private static $demandasNoImportadas = [];

    public function model(array $row)
    {

        if (!isset($row['ruc_empleador'])) {
            return null; // O manejar el caso en que no haya 'ruc_empleador'
        }

        $IdsLetras = [
            'A' => "Ana Pacheco",
            'X' => "Ximena Zavaleta",
            'F' => "Fátima Rodríguez",
            'D' => "Deiner Diaz",
            'C' => "Cristhofer Fernández",
            'L' => "Lesslie Calle",
            'E' => "Edita Campos",
            'ED' => "Edgar Pacheco",
        ];


        $fecha_presentacion = null;

        $numdemanda = (string)$row['num_demanda'];
        
        $sub_num = explode("; ", $numdemanda);

        $id_demanda = DemandaProfuturoNumero::where('NUM_DEMANDA', $sub_num)->first();
         
        if($id_demanda){
            $demandaprofuturo = $id_demanda->demandaProfuturo;
            if ($demandaprofuturo) {
                if (isset($row['fe_emision'])) {
                    $fe_emision = $row['fe_emision'];
                    if (is_numeric($fe_emision)) {
                        $fe_emision_date = Date::excelToDateTimeObject($fe_emision);
                        $demandaprofuturo->FE_EMISION = $fe_emision_date->format('Y-m-d');
                    } else {
                        try {
                            $fe_emision_date = Carbon::parse($fe_emision);
                            $demandaprofuturo->FE_EMISION = $fe_emision_date->format('Y-m-d');
                        } catch (\Exception $e) {
                            $demandaprofuturo->FE_EMISION = null; // O algún valor predeterminado
                        }
                    }
                }
                if (isset($row['cod_estudio'])) {
                    $demandaprofuturo->COD_ESTUDIO = $row['cod_estudio'];
                }
                if (isset($row['total_demandado'])) {
                    $demandaprofuturo->TOTAL_DEMANDADO = $row['total_demandado'];
                }
                if (isset($row['tipo_deuda'])) {
                    $demandaprofuturo->TIP_DEUDA = Deuda::where('DESCRIPCION_DEUDA', $row['tipo_deuda'])->value('ID_DEUDA');
                }
                if (isset($row['cod_estudio'])) {
                    $demandaprofuturo->COD_ESTUDIO = $row['cod_estudio'];
                }
                if (isset($row['codigo_unico_expediente'])) {
                    $demandaprofuturo->CODIGO_UNICO_EXPEDIENTE = $row['codigo_unico_expediente'];
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
                    $demandaprofuturo->NRO_EXPEDIENTE = $row['expediente'];
                }
                if (isset($row['ano'])) {
                    $demandaprofuturo->AÑO = $row['ano'];
                }
                if (isset($row['estado'])) {
                    $demandaprofuturo->ID_ESTADO = Estado::where('ESTADO', $row['estado'])->value('ID_ESTADO');
                }

                if (isset($row['ubicacion_proceso'])) {
                    $demandaprofuturo->ID_UBIPROCESO = UbiProceso::where('UBIPROCESO', $row['ubicacion_proceso'])->value('ID_UBIPROCESO');
                }

                $juzgado = Juzgado::where('ID_JUZGADO', $demandaprofuturo->ID_JUZGADO)->first();
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
                    $demandaprofuturo->ID_JUZGADO = $juzgado2->ID_JUZGADO;
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
                    $demandaprofuturo->ID_JUZGADO = $juzgado->ID_JUZGADO;
                }
                $demandaprofuturo->save();
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
                            EventoDemandaProfuturo::updateOrCreate(
                                [
                                    'ID_DEMANDAP' => $demandaprofuturo->ID_DEMANDAPRO,
                                    'CODIGO_EVENTO' => $codigo_evento,
                                    'FECHA_EVENTO' => $fecha_evento,
                                ],
                                [
                                    'RESOLUCION' => $resolucion,
                                    'ID_REGISTRO' => 1,
                                    'ID_UBIPROCESO' => $demandaprofuturo->ID_UBIPROCESO,
                                    'OBSERVACIONES' => $observaciones,
                                ]
                            );
                        }
                        $indice++;
                    }
                }
            }

            
        } else {
            try {
                if (isset($row['emision'])) {
                    $fe_emision = $row['emision'];
                    if (is_numeric($fe_emision)) {
                        $fe_emision_date = Date::excelToDateTimeObject($fe_emision);
                        $fe_emision = $fe_emision_date->format('Y-m-d');
                    } else {
                        try {
                            $fe_emision_date = Carbon::parse($fe_emision);
                            $fe_emision = $fe_emision_date->format('Y-m-d');
                        } catch (\Exception $e) {
                            $fe_emision = null; // O algún valor predeterminado
                        }
                    }
                }

                if(isset($row['cod_estudio'])) {
                    $cod_estudio = $row['cod_estudio']*10;
                }else{
                    $cod_estudio = null;
                }

                if (isset($row['total_demandado'])) {
                    $total_demandado = $row['total_demandado'];
                }else{
                    $monttotal_demandadoo_demanda = null;
                }

                if (isset($row['mto_deuda_actualizada'])) {
                    $mto_deuda_actualizada= $row['mto_deuda_actualizada'];
                }else{
                    $mto_deuda_actualizada = null;
                }

                if (isset($row['tipo_deuda'])) {
                    $tipo_deuda = $row['tipo_deuda'];
                }else{
                    $tipo_deuda = null;
                }

                if (isset($row['codigo_unico_expediente'])) {
                    $codigo_unico_expediente = $row['codigo_unico_expediente'];
                }else{
                    $codigo_unico_expediente = null;
                }

                if (isset($row['fecha_presentacion'])) {
                    $fecha_presentacion_date = $row['fecha_presentacion'];
                    if (is_numeric($fecha_presentacion_date)) {
                        $fecha_presentacion = Date::excelToDateTimeObject($fecha_presentacion_date);
                    } else {
                        try {
                            $fecha_presentacion = Carbon::parse($fecha_presentacion_date);
                        } catch (\Exception $e) {
                            $fecha_presentacion = null; // O algún valor predeterminado
                        }
                    }
                }

                if (isset($row['nro_expediente'])) {
                    $nro_expediente = $row['nro_expediente'];
                }else{
                    $nro_expediente = null;
                }

                if (isset($row['ano'])) {
                    $ano = $row['ano'];
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
                $afp = 2;

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

                $demandaprofuturo = new DemandaProfuturo([
                    'RUC_EMPLEADOR' => $ruc_empleador,
                    'FE_EMISION' => $fe_emision,
                    'COD_ESTUDIO' => $cod_estudio,
                    'TOTAL_DEMANDA' => $total_demandado,
                    'TIP_DEUDA' => $tipo_deuda,
                    'CODIGO_UNICO_EXPEDIENTE' => $codigo_unico_expediente,
                    'FECHA_PRESENTACION' => $fecha_presentacion,
                    'NRO_EXPEDIENTE	' => $nro_expediente,
                    'AÑO' => $ano,
                    'ID_JUZGADO' => $juzgado->ID_JUZGADO,
                ]);
                $demandaprofuturo->save();

                foreach ($sub_num as $key => $value) {
                    $demandaProfuturoNumero = new DemandaProfuturoNumero([
                        'NUM_DEMANDA' => $value,
                        'ID_DEMANDAPRO' => $demandaprofuturo->ID_DEMANDAPRO,
                    ]);
                    $demandaProfuturoNumero->save();
                }
                

                if (isset($row['tipo_deuda'])) {
                    $tip_deuda = (string) $row['tipo_deuda'];
                } else {
                    $tip_deuda = null;
                }

                if (isset($row['repro'])) {
                    $repro = $row['repro'];
                }else{
                    $repro = null;
                }
                
                $demanda = Demanda::Create([
                    'ID_DEMANDAPRO' => $demandaprofuturo->ID_DEMANDAPRO,
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
                            $eventodemandaprofuturo = new EventoDemandaProfuturo([
                                'ID_DEMANDAP' => $demandaprofuturo->ID_DEMANDAP,
                                'CODIGO_EVENTO' => $row['codigo_evento_' . $indice],
                                'RESOLUCION' => $row['resolucion_' . $indice] ?? "0",
                                'FECHA_EVENTO' => $fecha_evento,
                                'ID_REGISTRO' => 1,
                                'ID_UBIPROCESO' => $ubiproceso,
                                'OBSERVACIONES' => $row['observaciones_' . $indice] ?? null,
                            ]);

                            $eventodemandaprofuturo->save();
                        }

                        if ($indice == 1 && is_null($fecha_presentacion)) {
                            $demandaprofuturo->FECHA_PRESENTACION = $fecha_evento;
                            $demandaprofuturo->save();
                        }
                        $indice++;
                    }
                }
            }
            catch (\Exception $e) {
                //consola
                dd($e);

                self::$demandasNoImportadas[] = $row['num_demanda'];
            }
        }
        return $demandaprofuturo;
    }

    public static function getDemandasNoImportadas()
    {
        return self::$demandasNoImportadas;
    }
}



// if (isset($row['actividad'])) {
//     $actividadExistente = Actividad::where('ID_DEMANDAPRO', $demandaProfuturo->ID_DEMANDAPRO)->first();

//     if ($actividadExistente) {
//         $actividadExistente->delete();
//     }

//     $id_usuario = isset($IdsLetras[$row['actividad']] ) ? $IdsLetras[$row['actividad']] : null;

//     if ($id_usuario !== null) {
//         $id = User::where('name', $id_usuario)->value('id');
//         $actividad = new Actividad();
//         $actividad->ID_DEMANDAPRO = $demandaProfuturo->ID_DEMANDAPRO;
//         $actividad->ID_USUARIO = $id;
//         $actividad->ACTIVIDAD = ' Realizo a la Demanda Profuturo ' . $demandaProfuturo->NUM_DEMANDA;
//         $actividad->save();
//     }
//