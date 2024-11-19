<div>
@include('livewire.utilities.alerts')
    <x-slot name="header">
        <div class="section-header">
            <h1>Empresas</h1>
        </div>
    </x-slot>

    <div class="card">

        <div class="card-body">
          <div class="row mb-3">  
            <div class="col-md-4">
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
            <div class="col-md-8">
              @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('user'))
                <div class="form-group">
                    <label>Importar / Actualizar Data</label>
                    <br>
                    <div class="input-group">
                        <input type="file" class="form-control" wire:model="excel" id="excel" class="form-control-file" wire:loading.attr="disabled" wire:target="excel" aria-describedby="inputGroupFileAddon04" aria-label="Upload">
                        @if ($excel != null)
                            <button class="btn btn-outline-primary" type="button" id="excel" wire:click="importar" wire:loading.attr="disabled" wire:target="excel">Importar</button>
                        @endif
                    </div>
                    <div class="mt-2">
                        <small>
                            <strong>Notas:</strong>
                            @foreach($expectedColumns as $column)
                                {{ $column }} -
                            @endforeach
                        </small>
                    </div>
                    <br>
                    <label>Importar Cartera Pre-Judicial</label>
                    <br>
                    <div class="input-group">
                        <input type="file" class="form-control" wire:model="excel2" id="excel2" class="form-control-file" wire:loading.attr="disabled" wire:target="excel2" aria-describedby="inputGroupFileAddon04" aria-label="Upload">
                        @if ($excel2 != null)
                            <button class="btn btn-outline-primary" type="button" id="excel2" wire:click="importar2" wire:loading.attr="disabled" wire:target="excel2">Importar</button>
                        @endif
                    </div>
                    <div class="mt-2">
                        <small><strong>Nota:</strong>
                            @foreach($expectedColumns2 as $column)
                                {{ $column }} -
                            @endforeach
                        </small>
                    </div>
                  @if (session()->has('success'))
                    <div class="mt-3">
                      <button class="btn btn-outline-danger" wire:click="logs">Demandas no importadas</button>
                    </div>
                  @endif
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
                  <div wire:loading wire:target="importar2">
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
              @endif
            </div>
          </div>
            <div class="row mb-3">
              <div class="form-group">
                <button class="learn-more" wire:click="filtros">
                  <span class="circle" aria-hidden="true">
                  <span class="icon arrow"></span>
                  </span>
                  <span class="button-text">Mostrar Filtros</span>
                </button>
              </div>
              @if($showFilters)
                <fieldset class="bg-light" style="padding: 10px;">
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label for="filtroFechaInicioCarga">Ingresar Fecha de Carga (Inicio):</label>
                            <input type="date" id="filtroFechaInicioCarga" wire:model="filtroFechaInicioCarga" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label for="filtroFechaInicioCorreo">Ignorar de Envio Correo (Inicio):</label>
                            <input type="date" id="filtroFechaInicioCorreo" wire:model="filtroFechaInicioCorreo" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label for="filtroDepartamento">Filtrar por Departamento:</label>
                            <select id="filtroDepartamento" wire:model="filtroDepartamento" class="form-control">
                                <option value="">Todos</option>
                                @foreach ($departamentos as $departamento)
                                    <option value="{{ $departamento }}">{{ $departamento}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="filtroTipoEmpresa">Filtrar por Tipo:</label>
                            <select id="filtroTipoEmpresa" wire:model="filtroTipoEmpresa" class="form-control">
                                <option value="">Todos</option>
                                @foreach ($tipos as $tipo)
                                    @if ($tipo == 1){
                                        <option value="1">PRIVADA</option>
                                    }@elseif($tipo == 2){
                                        <option value="2">PUBLICA</option>
                                    }@endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="filtroTipoAFP">Filtrar por AFP:</label>
                            <select id="filtroTipoAFP" wire:model="filtroTipoAFP" class="form-control">
                                <option value="">Todos</option>
                                @foreach ($afps as $afp)
                                    @if ($afp == 1){
                                        <option value="1">PRIMA</option>
                                    }@elseif($afp == 2){
                                        <option value="2">PROFUTURO</option>
                                    }@endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label for="filtroFechaFinCarga">Ingresar Fecha de Carga (Fin):</label>
                            <input type="date" id="filtroFechaFinCarga" wire:model="filtroFechaFinCarga" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label for="filtroFechaFinCorreo">Ignorar de Envio Correo (Fin):</label>
                            <input type="date" id="filtroFechaFinCorreo" wire:model="filtroFechaFinCorreo" class="form-control">
                        </div>
                    </div>
                    
                      
                        
    
                      
                      
                      
                  <div class="col-md-3">
                    <label for="filtroCheck">Filtrar por Checks:</label>
                    <br>
                    <label>
                        <input type="checkbox" wire:model="filtroCorreoRL">
                        Mostrar los que tiene Correo
                    </label>
                    <br>
                    <label>
                        <input type="checkbox" wire:model="filtroEnvioDia">
                        Mostrar no Enviados x Hoy Dia
                    </label>
                    <br>
                    <label>
                        <input type="checkbox" wire:model="filtroEnvioMes">
                        Mostrar no Enviados x Mes
                    </label>
                  </div>
                </fieldset>
              @endif
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                          <th scope="col" width="9%">
                            <div class="btn-group" role="group" aria-label="Selection Options">
                                <button type="button" wire:click="$set('selectionMode', 'none')" class="btn btn-secondary">
                                    <i class="fas fa-times" title="Nada"></i>
                                </button>
                                <button type="button" wire:click="$set('selectionMode', 'page')" class="btn btn-secondary">
                                    <i class="fas fa-file-alt" title="Seleccionar Página"></i>
                                </button>
                                <!-- <button type="button" wire:click="$set('selectionMode', 'all')" class="btn btn-secondary">
                                    <i class="fas fa-check-double" title="Seleccionar Todo"></i>
                                </button> -->
                            </div>
                          </th>
                          <th scope="col" wire:click="sortBy('RUC_EMPLEADOR')" style="cursor: pointer">RUC</th>
                          <th scope="col" wire:click="sortBy('RAZON_SOCIAL')" style="cursor: pointer">Razon Social</th>
                          <th scope="col" wire:click="sortBy('RL_CORREO')" style="cursor: pointer">Correo Representante</th>
                          <th scope="col" width="5%" wire:click="sortBy('RL_TELEFONO')" style="cursor: pointer">Telefono Representante</th>
                          <th scope="col" width="10%">Accion</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($empresas->isEmpty())
                            <tr>
                                <td colspan="5">No hay registros</td>
                            </tr>
                        @else
                            @foreach ($empresas as $empresa)
                            <tr wire:key="{{ $empresa->id }}">
                                <td>
                                    <div class="d-flex">
                                        <span>{{ ($empresas->currentpage() - 1) * $empresas->perpage() + $loop->index + 1 }}-</span>
                                        <input type="checkbox" 
                                            wire:click="toggleRucSeleccionado('{{ $empresa->RUC_EMPLEADOR }}', '{{ $empresa->RAZON_SOCIAL }}')" 
                                            {{ in_array($empresa->RUC_EMPLEADOR, array_column($rucSeleccionados, 'ruc')) ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td>{{ $empresa->RUC_EMPLEADOR }}</td>
                                <td>{{ $empresa->RAZON_SOCIAL }}</td>
                                <td>
                                @if($empresa->representante && $empresa->representante->RL_CORREO)
                                    {{ $empresa->representante->RL_CORREO }}
                                @else
                                    <span class="text-danger">No disponible</span>
                                @endif
                                </td>
                                <td>{{ $empresa->representante->RL_TELEFONO ?? 'No disponible' }}</td>
                                <td>
                                  <button class="btn btn-sm btn-link" data-toggle="tooltip"
                                    data-placement="top" title="Ver Envios" wire:click="viewEnvios({{ $empresa->RUC_EMPLEADOR }})">
                                    <i class="fas fa-envelope text-danger"></i>
                                  </button>
                                  <button class="btn btn-sm btn-link" data-toggle="tooltip"
                                    data-placement="top" title="Detalle" wire:click="viewDetalle({{ $empresa->RUC_EMPLEADOR }})">
                                    <i class="fas fa-eye text-primary"></i>
                                  </button>
                                  
                                </td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
                {{ $empresas->links() }}
            </div>    
        </div>
        <div class="card-footer">
            <p>
            @if (count($rucSeleccionados) > 0)
                <button class="btn btn-primary" data-toggle="modal" data-target="#PanelCorreo">Enviar Correo</button>
            @endif
            </p>
            <div data-tooltip="Exportar" class="button btn-primary" data-toggle="modal" data-target="#ExportModal">
                <div class="button-wrapper">
                    <div class="text">Exportar</div>
                    <span class="icon">
                        <svg viewBox="0 0 600 512" class="bi bi-cart2" fill="currentColor" height="16" width="16" xmlns="http://www.w3.org/2000/svg">
                            <path d="M0 64C0 28.7 28.7 0 64 0H224V128c0 17.7 14.3 32 32 32H384V288H216c-13.3 0-24 10.7-24 24s10.7 24 24 24H384V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V64zM384 336V288H494.1l-39-39c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l80 80c9.4 9.4 9.4 24.6 0 33.9l-80 80c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l39-39H384zm0-208H256V0L384 128z"/></path>
                        </svg>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->

    <!-- Modal Detalles-->

    <div wire:ignore.self class="modal fade" id="PanelVerDetalles" tabindex="-1" role="dialog" aria-labelledby="modalDetallesLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetallesLabel">Detalles de la Empresa</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="details-section">
                                <p><strong>RUC:</strong> {{$verRuc}}</p>
                                <p><strong>Razon Social:</strong> {{$verRazonSocial}}</p>
                                <p><strong>Tipo Empresa:</strong> {{$verTipoEmpresa}}</p>
                                <p><strong>Direccion:</strong> {{$verDireccion}}</p>
                                <p><strong>Referencia:</strong> {{$verReferencia}}</p>
                                <p><strong>UBIGEO:</strong> {{$verDistrito}} - {{$verProvincia}} - {{$verDepartamento}}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="details-section">
                                <p><strong>Nombre Representante:</strong> {{$verRpL}}</p>
                                <p><strong>Telefono Representante:</strong> {{$verTelefono}}</p>
                                <p><strong>Correo Representante:</strong> {{$verCorreo}}</p>
                                <p><strong>Contactos adicionales:</strong>
                                  <ul>
                                      @forelse($datosEmpresa as $dato)
                                          @if($dato['TELEFONO'] != null)
                                              <li>{{$dato['TELEFONO']}}</li>
                                          @endif
                                      @empty
                                          <li>No hay datos adicionales.</li>
                                      @endforelse
                                      @forelse($datosEmpresa as $dato)
                                          @if($dato['CORREO'] != null)
                                              <li>{{$dato['CORREO']}}</li>
                                          @endif
                                      @empty
                                          <li>No hay datos adicionales.</li>
                                      @endforelse
                                  </ul>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Enviar Correo-->
    <div wire:ignore.self class="modal fade" id="PanelCorreo" tabindex="-1" role="dialog" aria-labelledby="modalDetallesLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="modalDetallesLabel">Enviar Correos</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body">
                  <form>
                      <div class="row">
                          <div class="col-md-6">
                              <label for="tipoAFP">Tipo de AFP:</label>
                              <select class="form-control" wire:model="tipoAFP" id="tipoAFP">
                                  <option value="PRIMA">PRIMA</option>
                                  <option value="PROFUTURO">PROFUTURO</option>
                              </select>
                          </div>
                          <div class="col-md-6">
                              <label for="tipoCorreo">Tipo de Deuda:</label>
                              <select class="form-control" wire:model="tipoCorreo" id="tipoCorreo">
                                  <option value="CJ_GENERAL">C.J. GENERAL</option>
                                  <option value="CJ_DEUDA_REAL">C.J. DEUDA REAL</option>
                                  <option value="CPREJ_DSP">C. PRE-J. DSP</option>
                                </select>
                          </div>
                      </div>
                      <br>
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" wire:model="enviarExtra" id="enviarExtra">
                        <label class="form-check-label" for="enviarExtra">Enviar a los correos relacionados</label>
                      </div>
                        <div class="form-group">
                            <label for="correoAsunto">
                                Destinatario(s): ({{ count($rucSeleccionados) }})
                            </label>
                            <div style="max-height: 100px; overflow-y: auto; border: 1px solid #ccc; padding: 10px;">
                                @foreach ($rucSeleccionados as $empresa)
                                    <p style="margin: 0;">
                                        <strong>{{ $empresa['ruc'] }}</strong> - {{ $empresa['razon_social'] }}
                                    </p>
                                @endforeach
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="correoAsunto">Asunto:</label>
                            <input type="text" class="form-control" wire:model="correoAsunto" id="correoAsunto">
                        </div>
                        
                        <div class="form-group">
                            <label for="correoMensaje">Mensaje:</label>
                            <textarea wire:model="correoMensaje" id="correoMensaje" style="width: 100%; height: 300px;"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="correoFirma">Firma:</label><br>
                            <div wire:model="correoFirma" id="correoFirma">
                                {!! nl2br(e($correoFirma)) !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="correoCC">Correos en Copia (CC):</label>
                            <input type="text" class="form-control" wire:model="correoCC" id="correoCC" placeholder="correo1,correo2">
                            <small class="form-text text-muted">Introduce correos separados por comas</small>
                        </div>
                  </form>
              </div>
              <div class="modal-footer">
                  <button type="button" wire:click="enviarCorreo" wire:loading.attr="disabled" class="contactButton">
                        Enviar
                        <div class="iconButton">
                            <svg height="24" width="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M16.172 11l-5.364-5.364 1.414-1.414L20 12l-7.778 7.778-1.414-1.414L16.172 13H4v-2z" fill="currentColor"></path>
                            </svg>
                        </div>
                    </button>

                  <button type="button" class="btn btn-primary" data-dismiss="modal">Cerrar</button>
              </div>
                
          </div>
          <div wire:loading wire:target="enviarCorreo">
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
  </div>


<!-- <div class="form-check">
                            <input class="form-check-input" type="checkbox" wire:model="enviarWhatsapp" id="enviarWhatsapp">
                            <label class="form-check-label" for="enviarWhatsapp">Enviar también a WhatsApp</label>
                        </div> -->

    <!-- Modal Ver Correos-->
    <div wire:ignore.self class="modal fade" id="PanelVerCorreos" tabindex="-1" role="dialog" aria-labelledby="modalDetallesLabel" aria-hidden="true" >
        <div class="modal-dialog modal-dialog-centered" role="document" >
            <div class="modal-content">
                <!-- Encabezado del modal -->
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetallesLabel"><strong>Correos Enviados</strong></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <!-- Cuerpo del modal -->
                <div class="modal-body">
                    @if(count($verRegistrosAsociados) > 0)
                        <ul class="list-group">
                            @foreach($verRegistrosAsociados as $fecha)
                                <li class="list-group-item">{{ $fecha }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p>No hay registros de correo enviado.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>


    <!-- Modal Exportar-->

    <div wire:ignore.self class="modal fade" id="ExportModal" tabindex="-1" role="dialog" aria-labelledby="modalDetallesLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Exportar Envios de Empresas</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <div class="modal-body">
            <p>Se exportará toda la data de las Empresas que se registro envios de correos</p>
            <h6>Filtros para Exportar</h6>
            
            <div class="form-group">
                <label for="mes">Mes</label>
                <select id="mes" class="form-control" wire:model="mes">
                    <option value="">Mostrar Todo</option>
                    <option value="01">Enero</option>
                    <option value="02">Febrero</option>
                    <option value="03">Marzo</option>
                    <option value="04">Abril</option>
                    <option value="05">Mayo</option>
                    <option value="06">Junio</option>
                    <option value="07">Julio</option>
                    <option value="08">Agosto</option>
                    <option value="09">Septiembre</option>
                    <option value="10">Octubre</option>
                    <option value="11">Noviembre</option>
                    <option value="12">Diciembre</option>
                </select>
            </div>

            <!-- Filtro de Año (opcional) 
            <div class="form-group">
                <label for="anio">Año</label>
                <input type="number" id="anio" class="form-control" wire:mode="anio" placeholder="Ingrese el año">
            </div>-->
          </div>

          <div class="modal-footer">
            <button class="btn btn-primary" wire:click="exportar" data-dismiss="modal">Exportar</button>
          </div>
        </div>
      </div>
    </div>
</div>

@push('scripts')
    <script>
        window.addEventListener('cerrarModal', event =>{
            $('#PanelCorreo').modal('hide');       
        });

        window.addEventListener('show-view-detalle-modal', event =>{
            $('#PanelVerDetalles').modal('show');       
        });

        window.addEventListener('show-view-correo-modal', event =>{
            $('#PanelVerCorreos').modal('show');
        });

        window.addEventListener('show-export-modal', event =>{
            $('#ExportModal').modal('show');
        });
    </script>
@endpush

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