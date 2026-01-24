<x-app-layout>
    <x-slot name="header">
        {{ __('Formulario de Cierre de Venta') }}
    </x-slot>

    <div class="container py-4">
        <div class="card shadow">
            <div class="card-body">
                <form method="POST" action="{{ route('sales.store') }}" enctype="multipart/form-data" id="saleForm">
                    @csrf
                    <input type="hidden" name="lead_id" value="{{ $lead->id }}">

                    <div class="row">
                        <!-- Columna Izquierda - Datos del Cliente -->
                        <div class="col-12 col-md-6">
                            <h5 class="text-primary mb-3">Datos del Cliente</h5>
                            <div class="mb-3">
                                <label class="form-label">Nombre del Cliente <span class="text-danger">*</span></label>
                                <input name="nombre_cliente" type="text" class="form-control" value="{{ old('nombre_cliente', $lead->nombre) }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Apellido del Cliente <span class="text-danger">*</span></label>
                                <input name="apellido_cliente" type="text" class="form-control" value="{{ old('apellido_cliente') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Correo Electronico <span class="text-danger">*</span></label>
                                <input name="email_cliente" type="email" class="form-control" value="{{ old('email_cliente', $lead->email) }}" required>
                            </div>
                             <div class="mb-3">
                                <label class="form-label">Numero de Telefono <span class="text-danger">*</span></label>
                                <input name="telefono_cliente" type="text" class="form-control" value="{{ old('telefono_cliente', $lead->telefono) }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Identificacion Personal</label>
                                <input name="identificacion_personal" type="text" class="form-control" value="{{ old('identificacion_personal') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Fuente</label>
                                <select name="fuente" class="form-select">
                                    <option value="">Seleccione una fuente</option>
                                    @foreach(['Instagram', 'Anuncios', 'YouTube', 'Referidos', 'CSM', 'Otros'] as $fuente)
                                        <option value="{{ $fuente }}" {{ old('fuente') == $fuente ? 'selected' : '' }}>{{ $fuente }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Columna Derecha - Datos de Pago -->
                        <div class="col-12 col-md-6">
                            <h5 class="text-primary mb-3">Datos de Pago</h5>
                            <div class="mb-3">
                                <label class="form-label">Metodo de Pago <span class="text-danger">*</span></label>
                                <select name="metodo_pago" class="form-select" required>
                                    <option value="">Seleccione un metodo</option>
                                    @foreach(['D LOCAL GO', 'WHOP', 'TRANSFERENCIA USD', 'TRANSFERENCIA PESOS', 'CRIPTO', 'EFECTIVO', 'WIRE', 'PAYPAL'] as $metodo)
                                        <option value="{{ $metodo }}" {{ old('metodo_pago') == $metodo ? 'selected' : '' }}>{{ $metodo }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Fecha de Cierre</label>
                                <input name="fecha_cierre" type="date" class="form-control" value="{{ old('fecha_cierre', date('Y-m-d')) }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Comprobante de Pago <span class="text-danger">*</span></label>
                                <input name="comprobante_pago[]" type="file" class="form-control" multiple required>
                                <small class="text-muted">Puede seleccionar multiples archivos. Formatos: JPG, PNG, PDF. Max 2MB cada uno.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Comentarios/Aclaraciones</label>
                                <textarea name="comentarios" class="form-control" rows="3">{{ old('comentarios') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Seccion de Contrato y Tipo -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5 class="text-primary mb-3">Informacion del Contrato</h5>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tipo de Contrato <span class="text-danger">*</span></label>
                                <select name="tipo_contrato" id="tipo_contrato" class="form-select" required>
                                    <option value="">Seleccione el tipo de contrato</option>
                                    <option value="low ticket" {{ old('tipo_contrato') == 'low ticket' ? 'selected' : '' }}>Low Ticket ($500 USD - precio fijo)</option>
                                    <option value="high ticket" {{ old('tipo_contrato') == 'high ticket' ? 'selected' : '' }}>High Ticket ($2,000 USD - precio fijo)</option>
                                    <option value="beca" {{ old('tipo_contrato') == 'beca' ? 'selected' : '' }}>Beca (monto variable/personalizado)</option>
                                    <option value="formacion_anexo" {{ old('tipo_contrato') == 'formacion_anexo' ? 'selected' : '' }}>Acuerdo de Formacion y Anexo (monto y cuotas personalizadas)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Contrato a Firmar</label>
                                <input type="text" id="contract_template_name" class="form-control" readonly
                                       value="Seleccione un tipo de contrato" style="background-color: #e9ecef;">
                                <input type="hidden" name="contract_template_id" id="contract_template_id" value="{{ old('contract_template_id') }}" required>
                                <small class="text-muted">El contrato se asigna automaticamente segun el tipo seleccionado.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Seccion Financiera -->
                    <div class="row mt-4" id="financial_section" style="display: none;">
                        <div class="col-12">
                            <h5 class="text-primary mb-3">
                                Informacion Financiera
                                <span id="price_config_badge" class="badge bg-secondary ms-2" style="font-size: 0.7em;"></span>
                            </h5>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Monto Total <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input name="monto_total" id="monto_total" type="number" step="0.01" min="0"
                                           class="form-control" value="{{ old('monto_total') }}" required>
                                    <select name="moneda" id="moneda" class="form-select" style="max-width: 100px;">
                                        <option value="USD" {{ old('moneda', 'USD') == 'USD' ? 'selected' : '' }}>USD</option>
                                        <option value="ARS" {{ old('moneda') == 'ARS' ? 'selected' : '' }}>ARS</option>
                                    </select>
                                </div>
                                <small id="monto_help" class="text-muted"></small>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Descuento (%)</label>
                                <input name="descuento_porcentaje" id="descuento_porcentaje" type="number" step="0.01" min="0" max="100"
                                       class="form-control" value="{{ old('descuento_porcentaje', 0) }}">
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Monto Final</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input name="monto_final" id="monto_final" type="number" step="0.01" min="0"
                                           class="form-control" value="{{ old('monto_final') }}" readonly>
                                    <span class="input-group-text" id="moneda_display">USD</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Formato de Pago <span class="text-danger">*</span></label>
                                <select name="tipo_acuerdo" id="tipo_acuerdo" class="form-select" required>
                                    <option value="">Seleccione un formato</option>
                                    <option value="Pago completo" {{ old('tipo_acuerdo') == 'Pago completo' ? 'selected' : '' }}>Pago completo (1 cuota)</option>
                                    <option value="Pago en 2 cuotas" {{ old('tipo_acuerdo') == 'Pago en 2 cuotas' ? 'selected' : '' }}>Pago en 2 cuotas</option>
                                    <option value="Pago en 3 cuotas" {{ old('tipo_acuerdo') == 'Pago en 3 cuotas' ? 'selected' : '' }}>Pago en 3 cuotas</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Dias entre cuotas</label>
                                <input name="dias_entre_cuotas" id="dias_entre_cuotas" type="number" min="1" max="90"
                                       class="form-control" value="{{ old('dias_entre_cuotas', 30) }}">
                                <small class="text-muted">Por defecto: 30 dias</small>
                            </div>
                        </div>
                    </div>

                    <!-- Seccion de Cuotas -->
                    <div class="row mt-4" id="cuotas_section" style="display: none;">
                        <div class="col-12">
                            <h5 class="text-primary mb-3">
                                Detalle de Cuotas
                                <span id="cuotas_mode_badge" class="badge bg-info ms-2" style="font-size: 0.7em;">Auto-calculadas</span>
                            </h5>
                        </div>

                        <!-- Cuota 1 -->
                        <div class="col-12 col-md-4 cuota-container" id="cuota_1_container">
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <strong>Cuota 1</strong>
                                    <span class="badge bg-primary float-end">Primera cuota</span>
                                </div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <label class="form-label">Monto</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input name="cuota_monto_1" id="cuota_monto_1" type="number" step="0.01" min="0"
                                                   class="form-control cuota-monto" data-cuota="1">
                                        </div>
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label">Fecha Vencimiento</label>
                                        <input name="cuota_fecha_1" id="cuota_fecha_1" type="date"
                                               class="form-control cuota-fecha" data-cuota="1">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cuota 2 -->
                        <div class="col-12 col-md-4 cuota-container" id="cuota_2_container" style="display: none;">
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <strong>Cuota 2</strong>
                                </div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <label class="form-label">Monto</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input name="cuota_monto_2" id="cuota_monto_2" type="number" step="0.01" min="0"
                                                   class="form-control cuota-monto" data-cuota="2">
                                        </div>
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label">Fecha Vencimiento</label>
                                        <input name="cuota_fecha_2" id="cuota_fecha_2" type="date"
                                               class="form-control cuota-fecha" data-cuota="2">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cuota 3 -->
                        <div class="col-12 col-md-4 cuota-container" id="cuota_3_container" style="display: none;">
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <strong>Cuota 3</strong>
                                </div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <label class="form-label">Monto</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input name="cuota_monto_3" id="cuota_monto_3" type="number" step="0.01" min="0"
                                                   class="form-control cuota-monto" data-cuota="3">
                                        </div>
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label">Fecha Vencimiento</label>
                                        <input name="cuota_fecha_3" id="cuota_fecha_3" type="date"
                                               class="form-control cuota-fecha" data-cuota="3">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Resumen de Cuotas -->
                        <div class="col-12">
                            <div class="alert alert-info" id="cuotas_summary">
                                <i class="bi bi-info-circle me-2"></i>
                                <span id="cuotas_summary_text">Seleccione el formato de pago para ver el detalle de cuotas.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Forma de Pago para Contrato -->
                    <div class="row mt-4" id="forma_pago_section" style="display: none;">
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">Forma de Pago (para el contrato) <span class="text-danger">*</span></label>
                                <textarea name="forma_de_pago" id="forma_de_pago" class="form-control" rows="3"
                                          placeholder="Describe la forma de pago acordada...">{{ old('forma_de_pago') }}</textarea>
                                <small class="text-muted">Este texto aparecera en el contrato. Ej: "El pago se realizara en 2 cuotas de $1,000 USD cada una."</small>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="numero_cuotas" id="numero_cuotas" value="1">

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <button type="submit" class="btn btn-primary">Registrar Venta</button>
                        <a href="{{ route('leads') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos
    const tipoContratoSelect = document.getElementById('tipo_contrato');
    const contractTemplateInput = document.getElementById('contract_template_id');
    const contractTemplateNameInput = document.getElementById('contract_template_name');
    const tipoAcuerdoSelect = document.getElementById('tipo_acuerdo');
    const financialSection = document.getElementById('financial_section');
    const cuotasSection = document.getElementById('cuotas_section');
    const formaPagoSection = document.getElementById('forma_pago_section');
    const montoTotalInput = document.getElementById('monto_total');
    const monedaSelect = document.getElementById('moneda');
    const descuentoInput = document.getElementById('descuento_porcentaje');
    const montoFinalInput = document.getElementById('monto_final');
    const diasEntreCuotasInput = document.getElementById('dias_entre_cuotas');
    const monedaDisplay = document.getElementById('moneda_display');
    const priceConfigBadge = document.getElementById('price_config_badge');
    const cuotasModeBadge = document.getElementById('cuotas_mode_badge');
    const numeroCuotasInput = document.getElementById('numero_cuotas');

    // Mapeo de tipo_contrato a contract_template_id
    const templateMapping = {
        'low ticket': { id: 18, name: 'Low Ticket' },
        'high ticket': { id: 20, name: 'High Ticket' },
        'beca': { id: 23, name: 'Beca' },
        'formacion_anexo': { id: 1, name: 'Acuerdo de Formacion y Anexo' }
    };

    // Precios por defecto (fallback si la API falla o no hay datos en BD)
    const defaultPrices = {
        'low ticket': {
            monto_base: 500.00,
            moneda: 'USD',
            monto_editable: false,
            cuotas_editables: false
        },
        'high ticket': {
            monto_base: 2000.00,
            moneda: 'USD',
            monto_editable: false,
            cuotas_editables: false
        },
        'beca': {
            monto_base: 0.00,
            moneda: 'USD',
            monto_editable: true,
            cuotas_editables: false
        },
        'formacion_anexo': {
            monto_base: 2000.00,
            moneda: 'USD',
            monto_editable: true,
            cuotas_editables: true
        }
    };

    // Estado de configuracion
    let currentConfig = {
        monto_base: 0,
        moneda: 'USD',
        monto_editable: true,
        cuotas_editables: false
    };

    // Manejar cambio de tipo de contrato
    tipoContratoSelect.addEventListener('change', function() {
        const tipoContrato = this.value;

        if (!tipoContrato) {
            financialSection.style.display = 'none';
            cuotasSection.style.display = 'none';
            formaPagoSection.style.display = 'none';
            contractTemplateInput.value = '';
            contractTemplateNameInput.value = 'Seleccione un tipo de contrato';
            return;
        }

        // Auto-seleccionar el template correspondiente
        const template = templateMapping[tipoContrato];
        if (template) {
            contractTemplateInput.value = template.id;
            contractTemplateNameInput.value = template.name;
        }

        // Obtener configuracion de precios desde la API o usar valores por defecto
        fetch(`{{ url('configuration/prices/api/get-price') }}?tipo_contrato=${encodeURIComponent(tipoContrato)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('API response not ok');
                }
                return response.json();
            })
            .then(data => {
                // Validar que la respuesta tenga los campos necesarios y monto_base no sea 0 para tipos con precio fijo
                if (data && data.monto_base !== undefined && data.monto_base !== null) {
                    // Si el API devuelve monto_base = 0 para low/high ticket, usar el fallback
                    if ((tipoContrato === 'low ticket' || tipoContrato === 'high ticket') && data.monto_base === 0) {
                        console.warn('API returned 0 for fixed price, using default');
                        currentConfig = defaultPrices[tipoContrato];
                    } else {
                        currentConfig = data;
                    }
                } else {
                    console.warn('Invalid API response, using defaults');
                    currentConfig = defaultPrices[tipoContrato] || defaultPrices['formacion_anexo'];
                }
                applyPriceConfiguration(currentConfig);
                financialSection.style.display = 'block';
                formaPagoSection.style.display = 'block';
            })
            .catch(error => {
                console.error('Error fetching price config:', error);
                // Usar valores por defecto segun el tipo de contrato
                currentConfig = defaultPrices[tipoContrato] || defaultPrices['formacion_anexo'];
                applyPriceConfiguration(currentConfig);
                financialSection.style.display = 'block';
                formaPagoSection.style.display = 'block';
            });
    });

    // Aplicar configuracion de precios
    function applyPriceConfiguration(config) {
        // Siempre establecer el monto base al cambiar el tipo de contrato
        // Para montos NO editables, siempre forzar el valor
        // Para montos editables, solo establecer si esta vacio
        if (!config.monto_editable) {
            montoTotalInput.value = parseFloat(config.monto_base).toFixed(2);
        } else if (montoTotalInput.value === '' || montoTotalInput.value === '0.00') {
            montoTotalInput.value = parseFloat(config.monto_base).toFixed(2);
        }

        // Establecer moneda
        monedaSelect.value = config.moneda;
        monedaDisplay.textContent = config.moneda;

        // Habilitar/deshabilitar edicion de monto
        montoTotalInput.readOnly = !config.monto_editable;

        // Estilo visual: fondo gris cuando no es editable
        if (config.monto_editable) {
            montoTotalInput.style.backgroundColor = '';
            montoTotalInput.style.cursor = 'text';
            priceConfigBadge.textContent = 'Monto editable';
            priceConfigBadge.className = 'badge bg-success ms-2';
            document.getElementById('monto_help').textContent = 'Puede modificar el monto segun lo acordado.';
        } else {
            montoTotalInput.style.backgroundColor = '#e9ecef';
            montoTotalInput.style.cursor = 'not-allowed';
            priceConfigBadge.textContent = 'Monto fijo';
            priceConfigBadge.className = 'badge bg-warning ms-2';
            document.getElementById('monto_help').textContent = 'El monto esta definido por la configuracion del sistema y no puede modificarse.';
        }

        // Actualizar badge de cuotas
        if (config.cuotas_editables) {
            cuotasModeBadge.textContent = 'Editables manualmente';
            cuotasModeBadge.className = 'badge bg-success ms-2';
        } else {
            cuotasModeBadge.textContent = 'Auto-calculadas';
            cuotasModeBadge.className = 'badge bg-info ms-2';
        }

        // Habilitar/deshabilitar campos de cuotas
        document.querySelectorAll('.cuota-monto').forEach(input => {
            input.readOnly = !config.cuotas_editables;
            if (config.cuotas_editables) {
                input.style.backgroundColor = '';
            } else {
                input.style.backgroundColor = '#e9ecef';
            }
        });

        // Recalcular monto final y cuotas
        calcularMontoFinal();
        calcularCuotas();
    }

    // Calcular monto final con descuento
    function calcularMontoFinal() {
        const montoTotal = parseFloat(montoTotalInput.value) || 0;
        const descuento = parseFloat(descuentoInput.value) || 0;

        const montoDescuento = montoTotal * (descuento / 100);
        const montoFinal = montoTotal - montoDescuento;

        montoFinalInput.value = montoFinal.toFixed(2);
        monedaDisplay.textContent = monedaSelect.value;

        return montoFinal;
    }

    // Calcular cuotas automaticamente
    function calcularCuotas() {
        const tipoAcuerdo = tipoAcuerdoSelect.value;
        const montoFinal = calcularMontoFinal();
        const diasEntreCuotas = parseInt(diasEntreCuotasInput.value) || 30;
        const today = new Date();

        // Ocultar todas las cuotas primero
        document.getElementById('cuota_1_container').style.display = 'none';
        document.getElementById('cuota_2_container').style.display = 'none';
        document.getElementById('cuota_3_container').style.display = 'none';
        cuotasSection.style.display = 'none';

        if (!tipoAcuerdo || montoFinal <= 0) {
            document.getElementById('cuotas_summary_text').textContent = 'Seleccione el formato de pago para ver el detalle de cuotas.';
            return;
        }

        cuotasSection.style.display = 'block';
        let numCuotas = 1;
        let cuotas = [];

        if (tipoAcuerdo === 'Pago completo') {
            numCuotas = 1;
            cuotas = [{ monto: montoFinal, fecha: formatDate(today) }];
            document.getElementById('cuota_1_container').style.display = 'block';
        } else if (tipoAcuerdo === 'Pago en 2 cuotas') {
            numCuotas = 2;
            const montoCuota = Math.floor(montoFinal / 2 * 100) / 100;
            const resto = montoFinal - montoCuota;
            cuotas = [
                { monto: montoCuota, fecha: formatDate(today) },
                { monto: resto, fecha: formatDate(addDays(today, diasEntreCuotas)) }
            ];
            document.getElementById('cuota_1_container').style.display = 'block';
            document.getElementById('cuota_2_container').style.display = 'block';
        } else if (tipoAcuerdo === 'Pago en 3 cuotas') {
            numCuotas = 3;
            const montoCuota = Math.floor(montoFinal / 3 * 100) / 100;
            const resto = montoFinal - (montoCuota * 2);
            cuotas = [
                { monto: montoCuota, fecha: formatDate(today) },
                { monto: montoCuota, fecha: formatDate(addDays(today, diasEntreCuotas)) },
                { monto: resto, fecha: formatDate(addDays(today, diasEntreCuotas * 2)) }
            ];
            document.getElementById('cuota_1_container').style.display = 'block';
            document.getElementById('cuota_2_container').style.display = 'block';
            document.getElementById('cuota_3_container').style.display = 'block';
        }

        numeroCuotasInput.value = numCuotas;

        // Solo llenar si no son editables o si estan vacios
        if (!currentConfig.cuotas_editables) {
            cuotas.forEach((cuota, index) => {
                const num = index + 1;
                document.getElementById(`cuota_monto_${num}`).value = cuota.monto.toFixed(2);
                document.getElementById(`cuota_fecha_${num}`).value = cuota.fecha;
            });
        } else {
            // Para cuotas editables, solo llenar si estan vacios
            cuotas.forEach((cuota, index) => {
                const num = index + 1;
                const montoInput = document.getElementById(`cuota_monto_${num}`);
                const fechaInput = document.getElementById(`cuota_fecha_${num}`);
                if (!montoInput.value) {
                    montoInput.value = cuota.monto.toFixed(2);
                }
                if (!fechaInput.value) {
                    fechaInput.value = cuota.fecha;
                }
            });
        }

        // Actualizar resumen
        const moneda = monedaSelect.value;
        let summaryText = `Total: $${montoFinal.toFixed(2)} ${moneda} en ${numCuotas} cuota(s). `;
        if (numCuotas > 1) {
            summaryText += `Intervalo: ${diasEntreCuotas} dias entre cuotas.`;
        }
        document.getElementById('cuotas_summary_text').textContent = summaryText;

        // Auto-generar forma de pago para contrato
        autoGenerarFormaPago(cuotas, numCuotas, moneda);
    }

    // Auto-generar texto de forma de pago
    function autoGenerarFormaPago(cuotas, numCuotas, moneda) {
        const formaPagoTextarea = document.getElementById('forma_de_pago');

        // Solo auto-generar si el campo esta vacio o tiene el texto anterior auto-generado
        if (formaPagoTextarea.value === '' || formaPagoTextarea.dataset.autoGenerated === 'true') {
            let texto = '';

            if (numCuotas === 1) {
                texto = `Pago unico de $${cuotas[0].monto.toFixed(2)} ${moneda} al momento de la firma.`;
            } else {
                texto = `El pago se realizara en ${numCuotas} cuotas:\n`;
                cuotas.forEach((cuota, index) => {
                    const fechaFormateada = formatDateSpanish(cuota.fecha);
                    texto += `- Cuota ${index + 1}: $${cuota.monto.toFixed(2)} ${moneda} - Vencimiento: ${fechaFormateada}\n`;
                });
            }

            formaPagoTextarea.value = texto;
            formaPagoTextarea.dataset.autoGenerated = 'true';
        }
    }

    // Helpers
    function addDays(date, days) {
        const result = new Date(date);
        result.setDate(result.getDate() + days);
        return result;
    }

    function formatDate(date) {
        return date.toISOString().split('T')[0];
    }

    function formatDateSpanish(dateStr) {
        const date = new Date(dateStr + 'T00:00:00');
        const options = { day: 'numeric', month: 'long', year: 'numeric' };
        return date.toLocaleDateString('es-ES', options);
    }

    // Event listeners
    montoTotalInput.addEventListener('input', function() {
        calcularMontoFinal();
        if (!currentConfig.cuotas_editables) {
            calcularCuotas();
        }
    });

    descuentoInput.addEventListener('input', function() {
        calcularMontoFinal();
        if (!currentConfig.cuotas_editables) {
            calcularCuotas();
        }
    });

    monedaSelect.addEventListener('change', function() {
        monedaDisplay.textContent = this.value;
        calcularCuotas();
    });

    tipoAcuerdoSelect.addEventListener('change', calcularCuotas);
    diasEntreCuotasInput.addEventListener('input', calcularCuotas);

    // Marcar forma de pago como editada manualmente
    document.getElementById('forma_de_pago').addEventListener('input', function() {
        this.dataset.autoGenerated = 'false';
    });

    // Verificar estado inicial (si hay valor previo, disparar el cambio)
    if (tipoContratoSelect.value) {
        tipoContratoSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
</x-app-layout>
