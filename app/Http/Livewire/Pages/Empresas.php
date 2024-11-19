<?php

namespace App\Http\Livewire\Pages;

use App\Exports\EmpresaCorreoExport;
use App\Models\Empresa;
use App\Models\EmpresaRpL;
use App\Models\EmpresaDato;
use App\Models\EmpresaEstudio;
use App\Models\EmpresaPrejudicial;
use App\Models\CarteraAsesor;
use App\Models\Periodo;
use App\Models\Departamento;
use App\Models\RegistroCorreo;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Component;
use App\Imports\EmpresaImport;
use App\Imports\EmpresaPreJudicialImport;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendMessage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use App\Mail\Correo;
use Excel;


class Empresas extends Component
{
    use WithFileUploads;
    use WithPagination;
    public $excel,$excel2, $correoMensaje, $correoMensajeR, $correoAsunto, $correoFirma;
    protected $paginationTheme = 'bootstrap';
    public $enviarWhatsapp = false;
    public $busqueda = '';
    public $campoSeleccionado = 'RUC_EMPLEADOR';
    public $camposBusqueda = [
        'RUC_EMPLEADOR' => 'RUC',
        'RAZON_SOCIAL' => 'Razon Social',

    ];
    
    public $expectedColumns = ['ruc_empleador', 'razon_social', 'tipo_empresa', 'direccion', 'locali', 'referencia', 'distrito', 'provincia', 'departamento', 'representante_legal', 'rl_telefono', 'rl_correo', 'cod_estudio', 'id_ejecutivo', 'id_estado', 'afp', 'correo_', 'telefono_']; // Ajusta estos nombres según lo que necesites
    public $expectedColumns2 = ['ruc_empleador', 'total_general', 'gastos', 'asesor', 'periodo_']; // Ajusta según las columnas de tu segundo archivo


    public $rucSeleccionados = [];
    public $selectedPageCheckboxes = [];
    
    public $tipoAFP = 'PRIMA';
    public $tipoCorreo = 'CJ_GENERAL';
    
    public $sortBy = 'RUC_EMPLEADOR';
    public $sortDirection = 'asc';
    public $filtroTipoEmpresa, $filtroCorreoRL,$filtroDepartamento, $filtroEnvioDia, $filtroEnvioMes, $filtroFechaInicioCarga,$filtroFechaFinCarga, $filtroTipoAFP, $filtroFechaInicioCorreo,$filtroFechaFinCorreo;    
    public $mes = '';
    public $anio = '';

    public $selectionMode = 'n'; // Puede ser: 'none', 'page', 'all'
    

    public function render()
    {
        $query = Empresa::with(['representante', 'departamento']);
        $tipos = Empresa::select('ID_TIPO')->distinct()->get()->pluck('ID_TIPO')->toArray();
        $departamentos = Departamento::select('DEPARTAMENTO')->distinct()->get()->pluck('DEPARTAMENTO')->toArray();
        $afps = EmpresaEstudio::select('COD_AFP')->distinct()->get()->pluck('COD_AFP')->toArray(); // Obtener los códigos de AFP

        if ($this->busqueda) {
            $query->where($this->campoSeleccionado, 'LIKE', '%' . $this->busqueda . '%');
        }
        if ($this->filtroCorreoRL) {
            $query->whereHas('representante', function ($q) {
                $q->whereNotNull('RL_CORREO');
            });
        }
        if ($this->filtroFechaInicioCarga && $this->filtroFechaFinCarga) {
            $query->whereBetween('FECHA_CARGA', [$this->filtroFechaInicioCarga, $this->filtroFechaFinCarga]);
        }
        if ($this->filtroFechaInicioCorreo && $this->filtroFechaFinCorreo) {
            $query->whereDoesntHave('registroCorreo', function ($q) {
                $q->whereBetween('FECHA', [$this->filtroFechaInicioCorreo, $this->filtroFechaFinCorreo]);
            });
        }
        if ($this->filtroDepartamento) {
            $query->whereHas('departamento', function ($q) {
                $q->where('DEPARTAMENTO', $this->filtroDepartamento);
            });
        }
        if ($this->filtroTipoEmpresa) {
            $query->where('ID_TIPO', $this->filtroTipoEmpresa);
        }
        if ($this->filtroTipoAFP) {
            $query->whereHas('estudio', function ($q) {
                $q->where('COD_AFP', $this->filtroTipoAFP);
            });
        }
        if ($this->filtroEnvioDia) {
            $diaActual = now()->format('d');
            
            $query->whereDoesntHave('registroCorreo', function ($q) use ($diaActual) {
                $q->whereDay('FECHA', $diaActual);
            });
        }
        if ($this->filtroEnvioMes) {
            $mesActual = now()->format('m');
            
            $query->whereDoesntHave('registroCorreo', function ($q) use ($mesActual) {
                $q->whereMonth('FECHA', $mesActual);
            });
        }
        if ($this->sortBy === 'RL_CORREO' || $this->sortBy === 'RL_TELEFONO') {
            $query->leftJoin('Empresas_RpL', 'Empresas.RUC_EMPLEADOR', '=', 'Empresas_RpL.RUC_EMPLEADOR')
                ->orderBy('Empresas_RpL.'.$this->sortBy, $this->sortDirection)
                ->select('Empresas.*');
        } else {
            $query->orderBy($this->sortBy, $this->sortDirection);
        }

        $empresas = $query->paginate(15);

        return view('livewire.pages.empresas', [
            'empresas' => $empresas,
            'tipos' => $tipos,
            'departamentos' => $departamentos,
            'afps' => $afps,
        ]);
    }

    public function toggleRucSeleccionado($ruc, $razonSocial)
    {
        $key = array_search($ruc, array_column($this->rucSeleccionados, 'ruc'));
    
        if ($key === false) {
            $this->rucSeleccionados[] = [
                'ruc' => $ruc,
                'razon_social' => $razonSocial
            ];
        } else {
            unset($this->rucSeleccionados[$key]);
        }
    
        $this->rucSeleccionados = array_values($this->rucSeleccionados);
    }


    public function updatedSelectionMode()
    {
        if ($this->selectionMode === 'none') {
            $this->rucSeleccionados = [];
        } elseif ($this->selectionMode === 'page') {
            // Obtén los RUCs de la página actual
            $this->updatePageSelection();
        }// elseif ($this->selectionMode === 'all') {
           // $this->updateAllSelection();
        //}
    }

    protected function updatePageSelection()
    {
        $empresas = $this->getCurrentPageEmpresas();
        
        foreach ($empresas as $empresa) {
            $ruc = $empresa->RUC_EMPLEADOR;
            $razonSocial = $empresa->RAZON_SOCIAL;
            
            $key = array_search($ruc, array_column($this->rucSeleccionados, 'ruc'));
    
            if ($key === false) {
                $this->rucSeleccionados[] = [
                    'ruc' => $ruc,
                    'razon_social' => $razonSocial
                ];
            }
        }
        $this->rucSeleccionados = array_values($this->rucSeleccionados);
    }
    
    protected function getCurrentPageEmpresas()
    {
        return Empresa::with(['representante', 'departamento'])
        ->when($this->busqueda, function ($query) {
            $query->where($this->campoSeleccionado, 'LIKE', '%' . $this->busqueda . '%');
        })
        ->when($this->filtroCorreoRL, function ($query) {
            $query->whereHas('representante', function ($q) {
                $q->whereNotNull('RL_CORREO');
            });
        })
        ->when($this->filtroFechaInicioCarga && $this->filtroFechaFinCarga, function ($query) {
            $query->whereBetween('FECHA_CARGA', [$this->filtroFechaInicioCarga, $this->filtroFechaFinCarga]);
        })
        ->when($this->filtroFechaInicioCorreo && $this->filtroFechaFinCorreo, function ($query) {
            $query->whereDoesntHave('registroCorreo', function ($q) {
                $q->whereBetween('FECHA', [$this->filtroFechaInicioCorreo, $this->filtroFechaFinCorreo]);
            });
        })
        ->when($this->filtroDepartamento, function ($query) {
            $query->whereHas('departamento', function ($q) {
                $q->where('DEPARTAMENTO', $this->filtroDepartamento);
            });
        })
        ->when($this->filtroTipoEmpresa, function ($query) {
            $query->where('ID_TIPO', $this->filtroTipoEmpresa);
        })
        ->when($this->filtroTipoAFP, function ($query) {
            $query->whereHas('estudio', function ($q) {
                $q->where('COD_AFP', $this->filtroTipoAFP);
            });
        })
        ->when($this->filtroEnvioDia, function ($query) {
            $diaActual = now()->format('d');
            $query->whereDoesntHave('registroCorreo', function ($q) use ($diaActual) {
                $q->whereDay('FECHA', $diaActual);
            });
        })
        ->when($this->filtroEnvioMes, function ($query) {
            $mesActual = now()->format('m');
            $query->whereDoesntHave('registroCorreo', function ($q) use ($mesActual) {
                $q->whereMonth('FECHA', $mesActual);
            });
        })
        ->orderBy($this->sortBy, $this->sortDirection)
        ->paginate(15);
    }


    protected function updateAllSelection()
    {
        $empresas = $this->getFilteredEmpresas();
        $this->rucSeleccionados = $empresas->pluck('RUC_EMPLEADOR')->toArray();
    }

    

    protected function getFilteredEmpresas()
    {
        return Empresa::with(['representante', 'departamento'])
            ->when($this->busqueda, function ($query) {
                $query->where($this->campoSeleccionado, 'LIKE', '%' . $this->busqueda . '%');
            })
            ->when($this->filtroTipoEmpresa, function ($query) {
                $query->where('ID_TIPO', $this->filtroTipoEmpresa);
            })
            ->when($this->filtroFechaInicioCarga && $this->filtroFechaFinCarga, function ($query) {
                $query->whereDoesntHave('registroCorreo', function ($q) {
                    $q->whereBetween('FECHA', [$this->filtroFechaInicioCarga, $this->filtroFechaFinCarga]);
                });
            })
            ->when($this->filtroDepartamento, function ($query) {
                $query->whereHas('departamento', function ($q) {
                    $q->where('DEPARTAMENTO', $this->filtroDepartamento);
                });
            })
            ->when($this->filtroCorreoRL, function ($query) {
                $query->whereHas('representante', function ($q) {
                    $q->whereNotNull('RL_CORREO');
                });
            })
            ->when($this->filtroEnvioDia, function ($query) {
                $diaActual = now()->format('d');
                $query->whereDoesntHave('registroCorreo', function ($q) use ($diaActual) {
                    $q->whereDay('FECHA', $diaActual);
                });
            })
            ->when($this->filtroEnvioMes, function ($query) {
                $mesActual = now()->format('m');
                $query->whereDoesntHave('registroCorreo', function ($q) use ($mesActual) {
                    $q->whereMonth('FECHA', $mesActual);
                });
            })
                
            ->orderBy($this->sortBy, $this->sortDirection)
            ->get();
    }

    public function sortBy($field)
    {
        if ($field !== 'id') {
            if ($this->sortBy === $field) {
                $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                $this->sortDirection = 'asc';
            }

            $this->sortBy = $field;
        }
    }

    public $verRegistrosAsociados = [];

    public function viewEnvios($id)
    {
        $registrosCorreo = RegistroCorreo::where('RUC_EMPLEADOR', (string)$id)->get();
    
        if ($registrosCorreo->isNotEmpty()) {
            $this->verRegistrosAsociados = $registrosCorreo->pluck('FECHA')->toArray();
        } else {
            $this->verRegistrosAsociados = [];
        }
    
        $this->dispatchBrowserEvent('show-view-correo-modal');
    }

    public $verRuc, $verRazonSocial, $verCorreo, $verTelefono, 
    $verDepartamento, $verProvincia, $verDistrito, $verDireccion, 
    $verReferencia, $verTipoEmpresa, $verLocali, $verRpL, $datosEmpresa = [];

    public function viewDetalle($id)
    {
        
        $empresa = Empresa::with(['empresaDato'])->where('RUC_EMPLEADOR', strval($id))->first();
        $representante = EmpresaRpL::where('RUC_EMPLEADOR', strval($id))->first();

        $this->verRuc = $empresa->RUC_EMPLEADOR;
        $this->verRazonSocial = $empresa->RAZON_SOCIAL ?? 'No disponible';
        $this->verRpL = $representante->REPRESENTANTE_LEGAL ?? 'No disponible';
        $this->verCorreo = $representante->RL_CORREO ?? 'No disponible';
        $this->verTelefono = $representante->RL_TELEFONO ?? 'No disponible';
        $this->verDepartamento = $empresa->Departamento->DEPARTAMENTO ?? 'No disponible';
        $this->verProvincia = $empresa->Provincia->PROVINCIA ?? 'No disponible';
        $this->verDistrito = $empresa->Distrito->DISTRITO ?? 'No disponible';
        $this->verDireccion = $empresa->DIRECC ?? 'No disponible';
        $this->verReferencia = $empresa->REFERENCIA ?? 'No disponible';
        $this->verTipoEmpresa = $empresa->TipoEmpresa->NOMBRE_TIPO ?? 'No disponible';
        $this->dispatchBrowserEvent('show-view-detalle-modal');

        $this->datosEmpresa = $empresa->empresaDato->map(function ($dato) {
            return [
                'TELEFONO' => $dato->TELEFONO, // Adjust these keys based on actual EmpresaDato attributes
                'CORREO' => $dato->CORREO,
            ];
        });
        
    }

    public $loading = false;
    public $enviarExtra;


    public $correoCC;

    public function enviarCorreo()
    {
        try {
            $this->loading = true;

            $rucSeleccionados = array_map('strval', array_column($this->rucSeleccionados, 'ruc'));
    
            Empresa::whereIn('RUC_EMPLEADOR', $rucSeleccionados)
                ->chunk(10, function ($empresasSeleccionadas){
                    foreach ($empresasSeleccionadas as $empresa) {
                        $representante = EmpresaRpL::where('RUC_EMPLEADOR', (string)$empresa->RUC_EMPLEADOR)->first();
                        if ($representante && $representante->RL_CORREO) {
                            // Preparar datos del correo
                            $dataCorreo = $this->prepararDatosCorreo($empresa, $representante);

                            $correosCC = array_map('trim', explode(',', $this->correoCC));

                            $usuario = auth()->user();
                            // Enviar correo principal en cola
                            Mail::to($representante->RL_CORREO)
                            ->queue(
                                new Correo($dataCorreo['asunto'], $dataCorreo['mensaje'], $dataCorreo['firma'], $dataCorreo['imagenPath'], $usuario,$correosCC)
                            );
    
                            // Registrar el envío del correo
                            $this->registrarCorreoEnviado($empresa);
    
                            // Enviar correos adicionales si aplica
                            if ($this->enviarExtra) {
                                $this->enviarCorreosAdicionales($empresa, $dataCorreo);
                            }
                        }
                    }
                });
    
            session()->flash('success', 'Correos en proceso de envío.');
            $this->rucSeleccionados = [];
            $this->dispatchBrowserEvent('cerrarModal');
    
        } catch (\Exception $e) {
            session()->flash('error', 'Error al enviar el correo: ' . $e->getMessage());
            $this->dispatchBrowserEvent('cerrarModal');
        } finally {
            $this->loading = false;
        }
    }
    

    protected function prepararDatosCorreo($empresa, $representante)
    {
        $asunto = $this->correoAsunto;
        $mensaje = $this->correoMensaje;
        $firma = $this->correoFirma;
        $departamento = $empresa->Departamento->DEPARTAMENTO ?? 'No disponible';

        $asunto = str_replace(['[RUC_EMPLEADOR]', '[RAZON_SOCIAL]', '[DEPARTAMENTO]'], 
                            [$empresa->RUC_EMPLEADOR, $empresa->RAZON_SOCIAL, $departamento], 
                            $asunto);

        $mensaje = str_replace(['[RAZON_SOCIAL]', '[RUC_EMPLEADOR]'], 
                            [$empresa->RAZON_SOCIAL, $empresa->RUC_EMPLEADOR], 
                            $mensaje);

        $prejudicial = EmpresaPrejudicial::where('RUC_EMPLEADOR', (string)$empresa->RUC_EMPLEADOR)->first();
        if ($prejudicial) {
            $mensaje = str_replace(
                ['[TOTAL_GENERAL]', '[GASTOS]', '[ASESOR]', '[NUMERO_CUENTA]', '[CCI_CUENTA]', '[TELEFONO_ASESOR]', '[CORREO_ASESOR]', '[RAZON_SOCIAL]'], 
                [$prejudicial->TOTAL_GENERAL, $prejudicial->GASTOS, $prejudicial->CarteraAsesor->ASESOR, $prejudicial->CarteraAsesor->NUMERO_CUENTA, $prejudicial->CarteraAsesor->CCI_CUENTA, $prejudicial->CarteraAsesor->TELEFONO, $prejudicial->CarteraAsesor->CORREO_ASESOR, $empresa->RAZON_SOCIAL], 
                $mensaje
            );

            $periodo = Periodo::where('ID_PREJUDICIAL', $prejudicial->ID_PREJUDICIAL)->get();
            if ($periodo->isNotEmpty()) {
                $listaperiodo = $periodo->map(function ($p) {
                    return $p->PERIODO_MES . ': ' . $p->MONTO_PERIODO;
                })->implode(', ');

                $mensaje = str_replace('[PERIODO]', $listaperiodo, $mensaje);
            } else {
                $mensaje = str_replace('[PERIODO]', 'No disponible', $mensaje);
            }
        }

        return [
            'asunto' => $asunto,
            'mensaje' => $mensaje,
            'firma' => $this->correoFirma,
            'imagenPath' => public_path('img/Logo2.png')
        ];
    }

    protected function enviarCorreosAdicionales($empresa, $dataCorreo)
    {
        $correosAdicionales = EmpresaDato::where('RUC_EMPLEADOR', (string)$empresa->RUC_EMPLEADOR)->pluck('CORREO')->toArray();

        foreach ($correosAdicionales as $correo) {
            if ($correo) {
                Mail::to($correo)->queue(new Correo($dataCorreo));
            }
        }
    }

    protected function registrarCorreoEnviado($empresa)
    {
        $registroCorreo = new RegistroCorreo();
        $registroCorreo->RUC_EMPLEADOR = (string)$empresa->RUC_EMPLEADOR;
        $registroCorreo->TIPO_CORREO =  $this->tipoCorreo;
        $registroCorreo->FECHA = now(); // Fecha actual
        $registroCorreo->save();
    }
    
    public function descargarCorreosNoEnviados()
    {
        $empresasSeleccionadas = Empresa::whereIn('RUC_EMPLEADOR', $this->rucSeleccionados)->get();
        $correosNoEnviados = [];
        foreach ($empresasSeleccionadas as $empresa) {
            $destinatario = $empresa->CORREO;
            if($destinatario == null) {
                $correosNoEnviados[] = $empresa->RUC_EMPLEADOR . ' - ' . $empresa->RAZON_SOCIAL;
            }
        }
        if(count($correosNoEnviados) > 0) {
            $phpWord = new \PhpOffice\PhpWord\PhpWord();
            $section = $phpWord->addSection();
            $text = $section->addText('Empresas que no tienen correos');
            foreach ($correosNoEnviados as $correos) {
                $text = $section->addText($correos);
            }
            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $fileName = 'empresas_sin_correo.docx';
            $objWriter->save($fileName);
            return response()->download(public_path($fileName))->deleteFileAfterSend(true);
        }else {
            return null;
        }
    }

    public $NoImportadas = [];

    public function importar ()
    {
        try {
            $this->loading = true;
            $this->validate([
                'excel' => 'required|mimes:xlsx,xls',
            ]);
            $file = $this->excel->getRealPath();
            Excel::import(new EmpresaImport, $file);   
            session()->flash('success', 'Datos actualizados correctamente.'); 
            $this->excel = null;
            $this->dispatchBrowserEvent('close-modal');
            $import = new EmpresaImport();
            $this->NoImportadas = $import->getNoImportadas(); 
        } catch (\Exception $e) {
            session()->flash('error', 'Error al importar los datos: ' . $e->getMessage());
            $this->excel = null;
            $this->dispatchBrowserEvent('close-modal');
        } finally {
            $this->loading = false; // Desactivar el estado de carga
        }
    }
    
    public function importar2 ()
    {
        try {
            $this->loading = true;
            $this->validate([
                'excel2' => 'required|mimes:xlsx,xls',
            ]);
            $file = $this->excel2->getRealPath();
            Excel::import(new EmpresaPreJudicialImport, $file);   
            session()->flash('success', 'Datos actualizados correctamente.'); 
            $this->excel2 = null;
            $this->dispatchBrowserEvent('close-modal');
            $import = new EmpresaPreJudicialImport();
            $this->NoImportadas = $import->getNoImportadas(); 
        } catch (\Exception $e) {
            session()->flash('error', 'Error al importar los datos: ' . $e->getMessage());
            $this->excel2 = null;
            $this->dispatchBrowserEvent('close-modal');
        } finally {
            $this->loading = false; // Desactivar el estado de carga
        }
    }

    public function logs()
    {
        return $this->descargarNoImportadas();
    }

    public function descargarNoImportadas()
    {
        // $import = new DemandaPrimaImport();
            
        if (!empty($this->NoImportadas)) {
            $phpWord = new \PhpOffice\PhpWord\PhpWord();
            $section = $phpWord->addSection();
            $text = $section->addText('Empresas no importadas');
            foreach ($this->NoImportadas as $empresa) {
                // Asegúrate de que $empresa sea un arreglo con 'ruc' y 'error'
                if (is_array($empresa) && isset($empresa['ruc']) && isset($empresa['error'])) {
                    $text = 'RUC: ' . $empresa['ruc'] . ' - Error: ' . $empresa['error'];
                    $section->addText($text);
                } else {
                    // En caso de que no sea un arreglo, lo tratamos como un string directo
                    $section->addText($empresa);
                }
            }
            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');

            $filePath = public_path('empresas_no_importadas.docx');
        
            $objWriter->save($filePath);
        
            // Limpiamos las no importadas después de generar el archivo
            $this->NoImportadas = [];

            return response()->download($filePath)->deleteFileAfterSend(true);

        } else {
            session()->flash('error', 'No hay empresas que no fueron importadas para descargar.');
            return null;
        }
    }

    

    public function mount()
    {
        $this->correoFirma = auth()->user()->name . "\nEJECUTIVO DE COBRANZA - AREA LEGAL\nFERNÁNDEZ NUÑEZ ASOCIADOS SRL";
        $this->actualizarMensaje();
        //\n\n[1] De conformidad con lo regulado por el artículo 35° del D.S. 054-97-EF (TUO de la Ley del Sistema Privado de Administración de Fondos de Pensiones). \n[2] Documentos idóneos que se encuentran regulados en el artículo 38° D.S. 054-97-EF (TUO de la Ley del Sistema Privado de Administración de Fondos de Pensiones).
    }

    public function updatedTipoAFP($value)
    {
        $this->actualizarMensaje();
    }

    public function updatedTipoCorreo($value)
    {
        $this->actualizarMensaje();
    }

    public function actualizarMensaje()
    {
        $tipoAFP = $this->tipoAFP;
        $tipoCorreo = $this->tipoCorreo;

        if($tipoCorreo== 'CJ_DEUDA_REAL'){
            $filePath = resource_path('messages/deuda_real_message.txt');
            $this->correoAsunto = str_replace('[TIPO_AFP]', $tipoAFP, "COBRANZA JUDICIAL [TIPO_AFP] AFP - RUC [RUC_EMPLEADOR] - [RAZON_SOCIAL] - [DEPARTAMENTO]");
        }elseif ($tipoCorreo== 'CPREJ_DSP'){
            $filePath = resource_path('messages/cartera_administrativa_message.txt');
            $this->correoAsunto = str_replace('[TIPO_AFP]', $tipoAFP, "DETALLE DE DEUDA/ COBRANZA PRE JUDICIAL [TIPO_AFP] AFP/ [RAZON_SOCIAL] [RUC_EMPLEADOR]");
        }else {
            $filePath = resource_path('messages/default_message.txt');
            $this->correoAsunto = str_replace('[TIPO_AFP]', $tipoAFP, "COBRANZA JUDICIAL [TIPO_AFP] AFP - RUC [RUC_EMPLEADOR] - [RAZON_SOCIAL] - [DEPARTAMENTO]");
        }
        

        // Leer el contenido del archivo
        if (file_exists($filePath)) {
            $mensaje = file_get_contents($filePath);
        } else {
            $mensaje = '';
        }

        $this->correoMensaje = str_replace('[TIPO_AFP]', $tipoAFP, $mensaje); 
    }

    public $showFilters = false;

    public function filtros()
    {
        $this->showFilters = !$this->showFilters;
    }


    public function exportar()
    {
        try{
            $query = Empresa::with(['representante', 'departamento']);
            $tipos = Empresa::select('ID_TIPO')->distinct()->get()->pluck('ID_TIPO')->toArray();
            $departamentos = Departamento::select('DEPARTAMENTO')->distinct()->get()->pluck('DEPARTAMENTO')->toArray();
            $afps = EmpresaEstudio::select('COD_AFP')->distinct()->get()->pluck('COD_AFP')->toArray(); // Obtener los códigos de AFP

            if ($this->busqueda) {
                $query->where($this->campoSeleccionado, 'LIKE', '%' . $this->busqueda . '%');
            }

            if ($this->filtroDepartamento) {
                $query->whereHas('departamento', function ($q) {
                    $q->where('DEPARTAMENTO', $this->filtroDepartamento);
                });
            }
            if ($this->filtroFechaInicioCarga && $this->filtroFechaFinCarga) {
            $query->whereBetween('FECHA_CARGA', [$this->filtroFechaInicioCarga, $this->filtroFechaFinCarga]);
            }
            
            if ($this->filtroFechaInicioCorreo && $this->filtroFechaFinCorreo) {
                $query->whereDoesntHave('registroCorreo', function ($q) {
                    $q->whereBetween('FECHA', [$this->filtroFechaInicioCorreo, $this->filtroFechaFinCorreo]);
                });
            }

            if ($this->filtroTipoEmpresa) {
                $query->where('ID_TIPO', $this->filtroTipoEmpresa);
            }

            if ($this->filtroTipoAFP) {
                $query->whereHas('estudio', function ($q) {
                    $q->where('COD_AFP', $this->filtroTipoAFP);
                });
            }

            if ($this->filtroCorreoRL) {
                $query->whereHas('representante', function ($q) {
                    $q->whereNotNull('RL_CORREO');
                });
            }

            if ($this->filtroEnvioDia) {
                $diaActual = now()->format('d');
                
                $query->whereDoesntHave('registroCorreo', function ($q) use ($diaActual) {
                    $q->whereDay('FECHA', $diaActual);
                });
            }
            
            if ($this->filtroEnvioMes) {
                $mesActual = now()->format('m');
                
                $query->whereDoesntHave('registroCorreo', function ($q) use ($mesActual) {
                    $q->whereMonth('FECHA', $mesActual);
                });
            } 


            if ($this->sortBy === 'RL_CORREO' || $this->sortBy === 'RL_TELEFONO') {
                $query->leftJoin('Empresas_RpL', 'Empresas.RUC_EMPLEADOR', '=', 'Empresas_RpL.RUC_EMPLEADOR')
                    ->orderBy('Empresas_RpL.'.$this->sortBy, $this->sortDirection)
                    ->select('Empresas.*');
            } else {
                $query->orderBy($this->sortBy, $this->sortDirection);
            }
            
            if ($this->mes) {
            
            $query->whereHas('registroCorreo', function ($q) {
                $q->whereMonth('FECHA', $this->mes);
            });
            }
    
            //if ($this->anio) {
                // Filtrar empresas que no tienen envíos en el año seleccionado
            //    $query->whereDoesntHave('registroCorreo', function ($q) {
            //        $q->whereYear('FECHA', $this->anio);
            //    });
            //}
                

            $data = $query->get();

            //contruir nombre del archivo con filtros
            $nombreArchivo = 'empresas';
            if(!empty($this->filtroTipoEmpresa)){
                $nombre .= '_tipo_'.$this->filtroTipoEmpresa;
            }
            if(!empty($this->filtroDepartamento)){
                $nombre = '_departamento_'.$this->filtroDepartamento;
            }

            $nombreArchivo = preg_replace("/[^A-Za-z0-9_\-]/", "", $nombreArchivo);

            $nombreArchivo .= '.xlsx';

            return Excel::download(new EmpresaCorreoExport($data, $this->mes), $nombreArchivo, \Maatwebsite\Excel\Excel::XLSX);

        } catch (\Exception $e) {
            session()->flash('error', 'Error al exportar los datos: ' . $e->getMessage());
            $this->dispatchBrowserEvent('close-modal');
        }
    }
}
?>