<div>
    @include('livewire.utilities.alerts')
    <x-slot name="header">
        <div class="section-header">
            <h1>DEMANDA PROFUTURO</h1>
        </div>
    </x-slot>

    <div class="card">
        <div class="card-header">
            <h4>Actividad de la Demanda Profuturo</h4>
        </div>
        
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="campoSeleccionado">Campo:</label>
                        <select id="campoSeleccionado" wire:model="campoSeleccionado" class="form-control">
                            @foreach ($camposBusqueda as $campo => $label)
                                <option value="{{ $campo }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="busqueda">Buscar:</label>
                        <input id="busqueda" wire:model="busqueda" type="text" class="form-control" placeholder="Buscar...">
                    </div>
                </div>

                <div class="col-md-6">
                    @if (auth()->user()->hasRole('admin'))
                        <div class="form-group">
                            <label>Importar</label>
                            <br>
                            <div class="input-group">
                            <input type="file" class="form-control" wire:model="excel" id="excel" class="form-control-file" wire:loading.attr="disabled" wire:target="excel" aria-describedby="inputGroupFileAddon04" aria-label="Upload">
                            @if ($excel != null)
                                <button class="btn btn-outline-primary" type="button" id="excel" wire:click="importar" wire:loading.attr="disabled" wire:target="excel">Importar</button>
                            @endif
                            </div>

                            <div wire:loading wire:target="importar">
                                <div class="overlay">
                                <div class="loader">
                                    <svg viewBox="0 0 80 80">
                                        <circle id="test" cx="40" cy="40" r="32"></circle>
                                    </svg>
                                </div>
                                <div class="loader triangle">
                                    <svg viewBox="0 0 86 80">
                                        <polygon points="43 8 79 72 7 72"></polygon>
                                    </svg>
                                </div>
                                <div class="loader">
                                    <svg viewBox="0 0 80 80">
                                        <rect x="8" y="8" width="64" height="64"></rect>
                                    </svg>
                                </div>
                                </div>
                            </div>

                        </div>  
                        <div class="form-group row mb-3">
                            <div class="col-md-6">
                                <label for="filtroAnos">Filtrar por Año:</label>
                                <select id="filtroAnos" wire:model="filtroAnos" class="form-control">
                                    <option value="">Todos</option>
                                    @foreach ($años as $año)
                                        <option value="{{ $año }}">{{ $año }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="filtroEvento">Filtrar por Evento:</label>
                                <select id="filtroEvento" wire:model="filtroEvento" class="form-control">
                                    <option value="">Todos</option>
                                    @foreach($eventos as $evento)
                                        <option value="{{ $evento->CODIGO_EVENTO }}">{{ $evento->CODIGO_EVENTO }} - {{ $evento->DESCRIPCION_EVENTO }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>   
                     @endif
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th scope="col" width="5%">#</th>
                            <th scope="col">Nro Demanda</th>
                            <th scope="col">RUC</th>
                            <th scope="col">Razon Social</th>
                            <th scope="col">Codigo de Expediente</th>
                            <th scope="col">Accion</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($demandaprofuturo->isEmpty())
                            <tr>
                                <td colspan="5">No hay registros</td>
                            </tr>
                        @else
                            @foreach ($demandaprofuturo as $demandap)
                                <tr>
                                    <th>{{ ($demandaprofuturo->currentpage() - 1) * $demandaprofuturo->perpage() + $loop->index + 1 }}</th>
                                    <td>{{ $demandap->NUM_DEMANDA }}</td>
                                    <td>{{ $demandap->Empresa->RUC_EMPLEADOR }}</td>
                                    <td>{{ $demandap->Empresa->RAZON_SOCIAL }}</td>
                                    <td>{{ $demandap->CODIGO_UNICO_EXPEDIENTE }}</td>
                                    <td>
                                        <div class="d-flex">
                                        <!-- foreach (navigation_links as link) -->
                                            <button class="btn btn-sm btn-link" data-toggle="tooltip"
                                                data-placement="top" title="Ver"  wire:click="viewDemandaDetails({{ $demandap->ID_DEMANDAPRO }})">
                                                <i  class="fas fa-eye text-primary"></i>
                                            </button>
                                            @if (auth()->user()->hasRole('admin'))
                                            <button class="btn btn-sm btn-link" data-toggle="tooltip"
                                                data-placement="top" title="Agregar Cod" wire:click="addEventoView({{ $demandap->ID_DEMANDAPRO }})">
                                                <i  class="fas fa-plus text-success"></i>
                                            </button>
                                            <!-- <button class="btn btn-sm btn-link" data-toggle="tooltip"
                                                data-placement="top" title="Editar" wire:click="editarDemanda({{ $demandap->ID_DEMANDAP }})">
                                                <i  class="fas fa-edit text-info"></i>
                                            </button> -->
                                            <button class="btn btn-sm btn-link" data-toggle="tooltip"
                                                data-placement="top" title="Eliminar" wire:click="deleteConfirmation({{ $demandap->ID_DEMANDAPRO }})">
                                                <i  class="fas fa-trash text-danger"></i>
                                            </button>
                                            @elseif (auth()->user()->hasRole('user'))
                                            <button class="btn btn-sm btn-link" data-toggle="tooltip"
                                                data-placement="top" title="Agregar Cod" wire:click="addEventoView({{ $demandap->ID_DEMANDAPRO }})">
                                                <i class="fas fa-plus text-success"></i>
                                            </button>
                                            @endif
                                        <!-- endforeach -->
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
                {{ $demandaprofuturo->links() }}
            </div>    
        </div>
        <div class="card-footer">
            <p>
                <button class="btn btn-primary" data-toggle="modal" data-target="#ExportModal">Exportar</button>
            </p>
        </div>
    </div>

    <!-- Modal Detalles-->

    <div wire:ignore.self class="modal fade" id="viewDemandaModal" tabindex="-1" role="dialog" aria-labelledby="modalDetallesLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetallesLabel">Detalles de la Demanda</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="details-section">
                                <p><strong>Nro Demanda:</strong>
                                @foreach($demandasRelacionadas as $ver)
                                    - {{$ver->NUM_DEMANDA}} -
                                @endforeach
                                </p>
                                <p><strong>RUC:</strong> {{$verRuc}} - {{$verTipoEmpresa}}</p>
                                <p><strong>Razón Social:</strong> {{$verRazonSocial}}</p>
                                <p><strong>Código Único de Expediente:</strong> {{$verCodigoExpediente}}</p>
                                <p><strong>Monto Total de la Demanda:</strong> {{$verTotalDemandado}}</p>
                                <p><strong>Tipo de Deuda:</strong> {{$verTipoDeuda}}</p>
                                <p><strong>Fecha de Emisión:</strong> {{$verFeEmision}}</p>
                                <p><strong>Fecha de Presentacion:</strong> {{$verFechaPresentacion}}</p>
                                <p><strong>Código de Estudio:</strong> {{$verCodEstudio}} - {{$verNombreEst}}</p>
                                <p><strong>Secretario:</strong> {{$verSecretario}}</p>
                                <p><strong>Juzgado:</strong> {{$verJuzgado}} - {{$verDescripcionJuzgado}}</p>
                            </div>
                        </div>
                        <div class="col-md-7">
                            @if(isset($verEventos) && count($verEventos) > 0)
                                <div class="events-section">
                                    <p><strong>Eventos Asociados:</strong></p>
                                    <ul class="list-group">
                                        @foreach($verEventos as $evento)
                                            @if ($evento['codigo_evento'] != 0)
                                                @if ($evento['registro'] == 'CEJ')
                                                    <li class="list-group-item">
                                                        Res {{$evento['resolucion_evento']}} / {{$evento['codigo_evento']}} - {{$evento['nombre_evento']}} -  {{$evento['fecha_evento']}}
                                                        <button class="btn btn-sm btn-link" data-toggle="tooltip"
                                                            data-placement="top" title="{{$evento['registro']}} / {{$evento['ubicacion']}}">
                                                            <i class="fas fa-eye text-primary"></i>
                                                        </button>
                                                    </li>
                                                @else
                                                    <li class="list-group-item">
                                                        Res {{$evento['resolucion_evento']}} / {{$evento['codigo_evento']}} - {{$evento['nombre_evento']}} -  {{$evento['fecha_evento']}}
                                                        <button class="btn btn-sm btn-link" data-toggle="tooltip"
                                                            data-placement="top" title="{{$evento['registro']}}">
                                                            <i class="fas fa-eye text-primary"></i>
                                                        </button>
                                                    </li>
                                                @endif
                                            @else
                                                @if ($evento['registro'] == 'CEJ')
                                                    <li class="list-group-item">
                                                        Res {{$evento['resolucion_evento']}} / {{$evento['observacion_evento']}} -  {{$evento['fecha_evento']}}
                                                        <button class="btn btn-sm btn-link" data-toggle="tooltip"
                                                            data-placement="top" title="{{$evento['registro']}} / {{$evento['ubicacion']}}">
                                                            <i class="fas fa-eye text-primary"></i>
                                                        </button>
                                                    </li>
                                                @else
                                                    <li class="list-group-item">
                                                        Res {{$evento['resolucion_evento']}} / {{$evento['observacion_evento']}} -  {{$evento['fecha_evento']}}
                                                        <button class="btn btn-sm btn-link" data-toggle="tooltip"
                                                            data-placement="top" title="{{$evento['registro']}}">
                                                            <i class="fas fa-eye text-primary"></i>
                                                        </button>
                                                    </li>
                                                @endif
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                            @else
                                <p><em>No hay eventos asociados.</em></p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

        <!--Modal Detele-->

    <div wire:ignore.self class="modal fade" id="deleteDemandaModal" tabindex="-1" data-backdrop="static" data-keyboard="false" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmacion para eliminar</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pt-4 pb-4">
                    <h6>Estas seguro de eliminar esta demanda: {{$verNroDemanda}}</h6> 
                    <!-- foreac(demandasRelacionadas as ver)
                        - ver->NUM_DEMANDA - 
                        endforeah ?! -->
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-primary" wire:click="cancel()" data-dismiss="modal" aria-label="Close">Cancelar</button>
                    <button class="btn btn-sm btn-danger" wire:click="deleteDemandaData()">Si! Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <!--Modal Add Evento-->
    <div wire:ignore.self class="modal fade" id="agregarEventoModal" tabindex="-1" role="dialog" aria-labelledby="agregarEventoModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="agregarEventoModalLabel">Agregar Nuevo Evento</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mt-3">
                        <ul class="list-group">
                            @foreach($verEventosAsociados as $evento)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>Evento:</strong> {{$evento['resolucion_evento']}} / {{$evento['codigo_evento']}} - {{$evento['nombre_evento']}}
                                        <br>
                                        <strong>Fecha:</strong> {{$evento['fecha_evento']}}
                                        @if ($evento['registro'] == 'CEJ')
                                            <button class="btn btn-sm btn-link" data-toggle="tooltip"
                                                data-placement="top" title="{{$evento['registro']}} / {{$evento['ubicacion']}}">
                                                <i class="fas fa-eye text-primary"></i>
                                            </button>

                                        @else
                                            <button class="btn btn-sm btn-link" data-toggle="tooltip"
                                                data-placement="top" title="{{$evento['registro']}}">
                                                <i class="fas fa-eye text-primary"></i>
                                            </button>
                                        @endif
                                    </div>
                                    @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('user'))
                                    <div>
                                        <button class="btn btn-sm btn-link" data-toggle="tooltip" data-placement="top"
                                                title="Delete" wire:click="eliminarEvento({{$evento['codigo_evento']}})">
                                                <i class="fas fa-trash text-danger"></i>
                                        </button>
                                    </div>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                        <br>
                        <x-auth-validation-errors class="mb-4" :errors="$errors" userName="{{auth()->user()->name}}"
                            x-data="{ showError: true }"
                            x-show="showError"
                            x-init="setTimeout(() => { showError = false; }, 10000)"/>
                        <div class="form-group">
                            <label for="resolucion">Resolucion</label>
                            <input id="resolucion" type="text" class="form-control" wire:model='resolucion'>
                        </div>
                        <div class="form-group">
                            <label for="eventoSeleccionado">Selecciona un evento:</label>
                            <select class="form-control" id="codigoEvento" wire:model='codigoEvento'>
                                <option value="">Selecciona un evento</option>
                                @foreach($verEventosDisponibles as $evento2)
                                    <option value="{{$evento2['codigo']}}">{{$evento2['codigo']}} - {{$evento2['nombre']}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="fechaEvento">Fecha del Evento:</label>
                            <input id="fechaEvento" type="date" class="form-control" wire:model='fechaEvento'>
                        </div>
                        <div class="form-group">
                            <x-label for="idRegistro" :value="__('Tipo de Registro')" />
                            <select id="idRegistro" name="idRegistro" class="form-control" wire:model='idRegistro'>
                                <option value="">Selecciona el Registro</option>
                                @foreach ($verRegistro as $registro)
                                    <option value="{{ $registro->ID_REGISTRO }}">{{ $registro->MODO_REGISTRO }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if($idRegistro == 2)
                        <div class="form-group">
                            <x-label for="idUbicacion" :value="__('Ubicacion')" />
                            <select id="idUbicacion" name="idUbicacion" class="form-control" wire:model='idUbicacion'>
                                <option value="">Selecciona la Ubicacion</option>
                                @foreach ($verUbicacion as $ubi)
                                    <option value="{{ $ubi->ID_UBICACION }}">{{ $ubi->NOMBRE_UBICACION }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="form-group">
                            <label for="observaciones">Observaciones</label>
                            <input id="observaciones" type="text" class="form-control" wire:model='observaciones'>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" wire:click="agregarEventoDemanda()">Guardar Evento</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Exportar -->

    <div wire:ignore.self class="modal fade" id="ExportModal" tabindex="-1" data-backdrop="static" data-keyboard="false" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
        <div class="modal-dialog  modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Exportar Datos Prima</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <br>
                    <h6>Filtros para Exportar</h6>
                    
                    <div class="modal-footer">
                        <button class="btn btn-primary" wire:click="exportar" data-dismiss="modal">Exportar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
      button {
      position: relative;
      display: inline-block;
      cursor: pointer;
      outline: none;
      border: 0;
      vertical-align: middle;
      text-decoration: none;
      background: transparent;
      padding: 0;
      font-size: inherit;
      font-family: inherit;
      }

      button.learn-more {
      width: 12rem;
      height: auto;
      }

      button.learn-more .circle .icon {
      transition: all 0.45s cubic-bezier(0.65, 0, 0.076, 1);
      position: absolute;
      top: 0;
      bottom: 0;
      margin: auto;
      background: none;
      display: block;
      
      }

      button.learn-more .circle {
      transition: all 0.45s cubic-bezier(0.65, 0, 0.076, 1);
      position: relative;
      display: block;
      margin: 0;
      width: 2.75rem;
      height: 2.75rem;
      background: #282936;
      border-radius: 1.625rem;
      }

      button.learn-more .circle .icon.arrow {
      transition: all 0.45s cubic-bezier(0.65, 0, 0.076, 1);
      left: 0.625rem;
      width: 1.125rem;
      height: 0.125rem;
      background: none;
      }

      button.learn-more .circle .icon.arrow::before {
      position: absolute;
      content: "";
      top: -0.29rem;
      right: 0.0625rem;
      width: 0.625rem;
      height: 0.625rem;
      border-top: 0.125rem solid #fff;
      border-right: 0.125rem solid #fff;
      transform: rotate(45deg);
      }

      button.learn-more .button-text {
      transition: all 0.45s cubic-bezier(0.65, 0, 0.076, 1);
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      padding: 0.75rem 0;
      margin: 0 0 0 1.85rem;
      color: #282936;
      font-weight: 700;
      line-height: 1.6;
      text-align: center;
      text-transform: uppercase;
      }

      button:hover .circle {
      width: 100%;
      }

      button:hover .circle .icon.arrow {
      background: #fff;
      transform: translate(1rem, 0);
      }

      button:hover .button-text {
      color: #fff;
      }

    .overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        }
    .loader {
      --path: #2f3545;
      --dot: #5628ee;
      --duration: 3s;
      width: 44px;
      height: 44px;
      position: relative;
    }

    .loader:before {
      content: '';
      width: 6px;
      height: 6px;
      border-radius: 50%;
      position: absolute;
      display: block;
      background: var(--dot);
      top: 37px;
      left: 19px;
      transform: translate(-18px, -18px);
      animation: dotRect var(--duration) cubic-bezier(0.785, 0.135, 0.15, 0.86) infinite;
    }

    .loader svg {
      display: block;
      width: 100%;
      height: 100%;
    }

    .loader svg rect, .loader svg polygon, .loader svg circle {
      fill: none;
      stroke: var(--path);
      stroke-width: 10px;
      stroke-linejoin: round;
      stroke-linecap: round;
    }

    .loader svg polygon {
      stroke-dasharray: 145 76 145 76;
      stroke-dashoffset: 0;
      animation: pathTriangle var(--duration) cubic-bezier(0.785, 0.135, 0.15, 0.86) infinite;
    }

    .loader svg rect {
      stroke-dasharray: 192 64 192 64;
      stroke-dashoffset: 0;
      animation: pathRect 3s cubic-bezier(0.785, 0.135, 0.15, 0.86) infinite;
    }

    .loader svg circle {
      stroke-dasharray: 150 50 150 50;
      stroke-dashoffset: 75;
      animation: pathCircle var(--duration) cubic-bezier(0.785, 0.135, 0.15, 0.86) infinite;
    }

    .loader.triangle {
      width: 48px;
    }

    .loader.triangle:before {
      left: 21px;
      transform: translate(-10px, -18px);
      animation: dotTriangle var(--duration) cubic-bezier(0.785, 0.135, 0.15, 0.86) infinite;
    }

    @keyframes pathTriangle {
      33% {
        stroke-dashoffset: 74;
      }

      66% {
        stroke-dashoffset: 147;
      }

      100% {
        stroke-dashoffset: 221;
      }
    }

    @keyframes dotTriangle {
      33% {
        transform: translate(0, 0);
      }

      66% {
        transform: translate(10px, -18px);
      }

      100% {
        transform: translate(-10px, -18px);
      }
    }

    @keyframes pathRect {
      25% {
        stroke-dashoffset: 64;
      }

      50% {
        stroke-dashoffset: 128;
      }

      75% {
        stroke-dashoffset: 192;
      }

      100% {
        stroke-dashoffset: 256;
      }
    }

    @keyframes dotRect {
      25% {
        transform: translate(0, 0);
      }

      50% {
        transform: translate(18px, -18px);
      }

      75% {
        transform: translate(0, -36px);
      }

      100% {
        transform: translate(-18px, -18px);
      }
    }

    @keyframes pathCircle {
      25% {
        stroke-dashoffset: 125;
      }

      50% {
        stroke-dashoffset: 175;
      }

      75% {
        stroke-dashoffset: 225;
      }

      100% {
        stroke-dashoffset: 275;
      }
    }

    .loader {
      display: inline-block;
      margin: 0 16px;
    }
</style>

@push('scripts')
    <script>
        window.addEventListener('close-modal', event =>{
            $('#deleteDemandaModal').modal('hide');
            $('#agregarEventoModal').modal('hide');
            $('#ImportExportModal').modal('hide');
        });
        window.addEventListener('show-delete-confirmation-modal', event =>{
            $('#deleteDemandaModal').modal('show');
        });
        window.addEventListener('show-view-demanda-modal', event =>{
            $('#viewDemandaModal').modal('show');
        });
        window.addEventListener('show-add-evento-modal', event =>{
            $('#agregarEventoModal').modal('show');
        });
        
    </script>
@endpush
