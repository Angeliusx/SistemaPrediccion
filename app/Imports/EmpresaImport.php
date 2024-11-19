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
use App\Models\User;
use DateTime;



class EmpresaImport implements ToModel, WithHeadingRow
{
    private static $NoImportadas = [];

    public function model(array $row)
    {
        $ruc = (string)$row['ruc_empleador'];
        $empresa = Empresa::where('RUC_EMPLEADOR', $ruc)->first();
        if ($empresa) {
            if (isset($row['razon_social'])) {
                $empresa->RAZON_SOCIAL = $row['razon_social'];
            }
            if (isset($row['tipo_empresa'])) {
                if ($row['tipo_empresa'] == 'PRI' || $row['tipo_empresa'] == 'PRIVADO' || $row['tipo_empresa'] == 'PRIVADA'|| $row['tipo_empresa'] == 'PRIVADAS' || $row['tipo_empresa'] == 'PRIV') {
                    $empresa->ID_TIPO = 1;
                } elseif($row['tipo_empresa'] == 'PUB' || $row['tipo_empresa'] == 'PUBLICO' || $row['tipo_empresa'] == 'PUBLICA' || $row['tipo_empresa'] == 'PUBLICAS') {
                    $empresa->ID_TIPO = 2;
                }
            }
            if (isset($row['distrito'])) {
                $empresa->DISTRITO = Distrito::where('DISTRITO', $row['distrito'])->value('ID_DIST');
            }
            if (isset($row['provincia'])) {
                $empresa->PROVINCIA = Provincia::where('PROVINCIA', $row['provincia'])->value('ID_P');
            }
            if (isset($row['departamento'])) {
                $empresa->DEPARTAMENTO = Departamento::where('DEPARTAMENTO', $row['departamento'])->value('ID_D');
            }
            if (isset($row['direcc'])) {
                $empresa->DIRECC = (string) $row['direcc'];
            }
            if (isset($row['locali'])) {
                $empresa->LOCALI = (string) $row['locali'];
            }
            if (isset($row['referencia'])) {
                $empresa->REFERENCIA = (string) $row['referencia'];
            }
            if ($empresa->FECHA_CARGA == null){
                $empresa->FECHA_CARGA = (new DateTime('now'))->format('Y-m-d');
            }
            else {
                $empresa->FECHA_CARGA = (new DateTime('now'))->format('Y-m-d');
            }
            // (new DateTime('now'))->format('Y-m-d');
            $empresa->save();
            $representante = EmpresaRpL::where('RUC_EMPLEADOR', $ruc)->first();
            if ($representante){
                if (isset($row['representante_legal'])) {
                    $representante->REPRESENTANTE_LEGAL =$row['representante_legal'];
                }
                if(isset($row['rl_telefono'])){
                    if(strlen($row['rl_telefono']) <= 9){
                        $representante->RL_TELEFONO = (string) $row['rl_telefono'];
                    }
                }
                if(isset($row['rl_correo'])){
                    $representante->RL_CORREO = $row['rl_correo'];
                }
                $representante->save();
            } else {
                $representante = new EmpresaRpL([
                    'RUC_EMPLEADOR' => $ruc,
                    'REPRESENTANTE_LEGAL' => $row['representante_legal'] ?? null,
                    'RL_CORREO' => $row['rl_correo'] ?? null,
                    'RL_TELEFONO' => $row['rl_telefono'] ?? null,
                ]);
                $representante->save();
            }
            $estudios = EmpresaEstudio::where('RUC_EMPLEADOR', $ruc)->first();
            if ($estudios){
                if (isset($row['afp'])) {
                    if ($row['afp'] == 'PRIMA' || $row['afp'] == 'PRIMAS' || $row['afp'] == 'PRI') {
                        $estudios->COD_AFP = 1;
                    } elseif($row['afp'] == 'PROFUTURO' || $row['afp'] == 'PROFUTUROS' || $row['afp'] == 'PROF') {
                        $estudios->COD_AFP = 2;
                    }
                }
                if (isset($row['cod_estudio'])) {
                    if($estudios->COD_AFP == 2){
                        $estudios->COD_ESTUDIO = 1090;
                    }
                    $estudios->COD_ESTUDIO = $row['cod_estudio'];
                }
                if (isset($row['id_ejecutivo'])) {
                    $estudios->ID_EJECUTIVO = $row['id_ejecutivo'];
                }
                if (isset($row['id_estado'])) {
                    $estudios->ID_ESTADO = $row['id_estado'];
                }
                
                $estudios->save();
            } else {
                if (isset($row['afp'])) {
                    if ($row['afp'] == 'PRIMA' || $row['afp'] == 'PRIMAS' || $row['afp'] == 'PRI') {
                        $cod_afp = 1;
                    } elseif ($row['afp'] == 'PROFUTURO' || $row['afp'] == 'PROFUTUROS' || $row['afp'] == 'PROF') {
                        $cod_afp = 2;
                    } else {
                        $cod_afp = null;
                    }
                } else {
                    $cod_afp = null;
                }

                if (isset($row['cod_estudio'])) {
                    $cod_estudio = $row['cod_estudio'];
                } else {
                    $cod_estudio = null;
                }

                if (isset($row['id_ejecutivo'])) {
                    $id_ejecutivo = $row['id_ejecutivo'];
                } else {
                    $id_ejecutivo = null;
                }

                if (isset($row['id_estado'])) {
                    $id_estado = $row['id_estado'];
                } else {
                    $id_estado = 1;
                }
                $estudios = new EmpresaEstudio([
                    'RUC_EMPLEADOR' => $ruc,
                    'COD_ESTUDIO' => $cod_estudio,
                    'ID_EJECUTIVO' => $id_ejecutivo,
                    'ID_ESTADO' => $id_estado,
                    'COD_AFP' => $cod_afp,
                ]);
                $estudios->save();
            }
            $columnasRepetidas = ['correo'];
            foreach ($columnasRepetidas as $columna) {
                $indice = 1;
                $telefonosGuardados = EmpresaDato::where('RUC_EMPLEADOR', $ruc)->pluck('TELEFONO')->toArray();
                $correosGuardados = EmpresaDato::where('RUC_EMPLEADOR', $ruc)->pluck('CORREO')->toArray();
                while (isset($row[$columna . '_' . $indice])) {
                    if (!empty($row['correo_' . $indice])) {
                        $telefono = $row['telefono_' . $indice] ?? null;
                        $correo = $row['correo_' . $indice] ?? null;
                        if (!in_array($correo, $correosGuardados)) {
                            $empresadato = new EmpresaDato([
                                'RUC_EMPLEADOR' => $ruc,
                                'CORREO' => $correo,
                            ]);
                            $empresadato->save();
                            $correosGuardados[] = $correo;
                        }
                        if (strlen($telefono) <= 9 && !in_array($telefono, $telefonosGuardados)) {
                            $empresadato = new EmpresaDato([
                                'RUC_EMPLEADOR' => $ruc,
                                'TELEFONO' => $telefono,
                            ]);
                            
                            $empresadato->save();
                            $telefonosGuardados[] = $telefono;
                        } 
                    }	
                    $indice++;
                }
            }
            $telefonosGuardados = [];
            $correosGuardados = [];
        } else {
            try{          
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

                $empresa = new Empresa([
                    'RUC_EMPLEADOR' => $ruc,
                    'RAZON_SOCIAL' => $row['razon_social'],
                    'ID_TIPO' => $tipo_empresa,
                    'DIRECC' => $direccion,
                    'LOCALI' => $locali,
                    'REFERENCIA' => $referencia,
                    'DISTRITO' => $distrito,
                    'PROVINCIA' => $provincia,
                    'DEPARTAMENTO' => $departamento,
                    'FECHA_CARGA' => (new DateTime('now'))->format('Y-m-d'),
                ]);
                // (new DateTime('now'))->format('Y-m-d')
                $empresa->save();

                if (isset($row['representante_legal'])) {
                    $representante_legal = (string) $row['representante_legal'];
                } else {
                    $representante_legal = null;
                }

                if (isset($row['rl_telefono'])) {
                    $rl_telefono = (string) $row['rl_telefono'];
                } else {
                    $rl_telefono = null;
                }

                if (isset($row['rl_correo'])) {
                    $rl_correo = $row['rl_correo'];
                } else {
                    $rl_correo = null;
                }

                $representante = new EmpresaRpL([
                    'RUC_EMPLEADOR' => $ruc,
                    'REPRESENTANTE_LEGAL' => $representante_legal,
                    'RL_TELEFONO' => $rl_telefono,
                    'RL_CORREO' => $rl_correo,
                ]);
                $representante->save();

                if (isset($row['cod_estudio'])) {
                    $cod_estudio = $row['cod_estudio'];
                } else {
                    $cod_estudio = null;
                }

                if (isset($row['id_ejecutivo'])) {
                    $id_ejecutivo = $row['id_ejecutivo'];
                } else {
                    $id_ejecutivo = null;
                }

                if (isset($row['id_estado'])) {
                    $id_estado = $row['id_estado'];
                } else {
                    $id_estado = 1;
                }

                if (isset($row['afp'])) {
                    if ($row['afp'] == 'PRIMA' || $row['afp'] == 'PRIMAS' || $row['afp'] == 'PRI') {
                        $cod_afp = 1;
                    } elseif($row['afp'] == 'PROFUTURO' || $row['afp'] == 'PROFUTUROS' || $row['afp'] == 'PROF') {
                        $cod_afp = 2;
                    }
                } else {
                    $cod_afp = null;
                }

                if ($cod_afp=='PROFUTURO'){
                    $cod_estudio = 1090;
                }

                $estudios = new EmpresaEstudio([
                    'RUC_EMPLEADOR' => $ruc,
                    'COD_ESTUDIO' => $cod_estudio,
                    'ID_EJECUTIVO' => $id_ejecutivo,
                    'ID_ESTADO' => $id_estado,
                    'COD_AFP' => $cod_afp,
                ]);

                $estudios->save();

                $columnasRepetidas = ['correo'];

                foreach ($columnasRepetidas as $columna) {
                    $indice = 1;
                    $telefonosGuardados = [];
                    $correosGuardados = [];
                    while (isset($row[$columna . '_' . $indice])) {
                        if (!empty($row['correo_' . $indice])) {
                            $telefono = $row['telefono_' . $indice] ?? null;
                            $correo = $row['correo_' . $indice] ?? null;
                            if (!in_array($correo, $correosGuardados)) {
                                $empresadato = new EmpresaDato([
                                    'RUC_EMPLEADOR' => $ruc,
                                    'CORREO' => $correo,
                                ]);
                                
                                $empresadato->save();
                                
                                $correosGuardados[] = $correo;
                            }

                            if (strlen($telefono) <= 9 && !in_array($telefono, $telefonosGuardados)) {
                                $empresadato = new EmpresaDato([
                                    'RUC_EMPLEADOR' => $ruc,
                                    'TELEFONO' => $telefono,
                                ]);
                                
                                $empresadato->save();
                                $telefonosGuardados[] = $telefono;
                            } 
                            
                            
                        }	
                        $indice++;
                    }
                }
            }
            catch(\Exception $e){
                self::$NoImportadas[] = [
                    'ruc' => $ruc,
                    'error' => $e->getMessage()  // Guardar el mensaje de error
                ];
            }
        }
    }

    public static function getNoImportadas()
    {
        return self::$NoImportadas;
    }
}
?>
