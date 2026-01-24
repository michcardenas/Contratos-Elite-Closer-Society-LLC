<x-app-layout>
    <x-slot name="header">
        {{ __('Leads') }}
    </x-slot>

    <div class="py-12" style="padding-top: 0;">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Closer Daily Form Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4">
                <div class="p-4 text-gray-900">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            <i class="bi bi-file-text text-warning"></i>
                            Formulario Closer Daily
                        </h5>
                        <div id="closerDailyStatusBadge">
                            <span class="badge bg-secondary">Cargando...</span>
                        </div>
                    </div>

                    <!-- Informational Alert (shown when already submitted today) -->
                    <div id="closerDailyInfoAlert" class="alert alert-info d-none" role="alert">
                        <i class="bi bi-info-circle"></i>
                        <span id="closerDailyInfoMessage"></span>
                        <button type="button" class="btn btn-sm btn-outline-primary ms-2" id="showCloserDailyFormBtn">
                            Volver a llenar
                        </button>
                    </div>

                    <!-- Collapsible Form Container -->
                    <div id="closerDailyFormContainer" class="collapse">
                        <div class="border rounded p-3 bg-light">
                            <form id="closerDailyForm">
                                <div id="closerDailyFieldsContainer">
                                    <p class="text-center text-muted">Cargando formulario...</p>
                                </div>
                                <div class="mt-3 d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-secondary" id="cancelCloserDailyForm">
                                        Cancelar
                                    </button>
                                    <button type="submit" class="btn btn-primary" id="submitCloserDailyForm">
                                        <i class="bi bi-send"></i> Enviar Formulario
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Button to show form (when collapsed and not submitted today) -->
                    <div id="closerDailyShowFormContainer">
                        <button type="button" class="btn btn-warning" id="openCloserDailyFormBtn">
                            <i class="bi bi-file-text"></i> Llenar Closer Daily
                        </button>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h4 class="text-2xl font-semibold mb-4">Leads</h4>

                    <div class="border border-gray-300 rounded-lg">
                        <div class="overflow-x-auto">

                        <table id="leads-table" class="table-responsive w-full text-sm text-left text-gray-700">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                <tr class="border-b border-gray-300">
                                    <th class="px-6 py-3" data-priority="1">Acciones</th>
                                    <th class="px-6 py-3">Estado Pipeline</th>
                                    <th class="px-6 py-3">Nombre</th>
                                    <th class="px-6 py-3">Email</th>
                                    <th class="px-6 py-3">Teléfono</th>
                                    <th class="px-6 py-3">Instagram</th>
                                </tr>
                            </thead>

                            <tbody></tbody>
                        </table>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para comentarios -->
    <div class="modal fade" id="comentarioModal" tabindex="-1" aria-labelledby="comentarioModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="comentarioModalLabel">Añadir Comentario al Cambio de Estado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="comentarioForm">
                        <input type="hidden" id="leadId" name="lead_id">
                        <input type="hidden" id="statusId" name="status_id">
                        <div class="mb-3">
                            <label for="comentarioText" class="form-label">Comentario</label>
                            <textarea class="form-control" id="comentarioText" name="comentario" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="guardarComentario">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Usar componente reutilizable para modal de venta -->
    <x-sale-details-modal />

    <!-- Modal para historial de cambios -->
    <div class="modal fade" id="logsModal" tabindex="-1" aria-labelledby="logsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Historial de Cambios de Estado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Tipo</th>
                                <th>Estado anterior</th>
                                <th>Estado nuevo</th>
                                <th>Comentario</th>
                                <th>Usuario</th>
                                <th>Fecha</th>
                                <th>Soportes</th>
                            </tr>
                        </thead>
                        <tbody id="logsTableBody">
                            <tr><td colspan="7" class="text-center">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
    $(document).on('click', '.view-logs-btn', function () {
        const leadId = $(this).data('lead-id');
        $('#logsModal').modal('show');
        $('#logsTableBody').html('<tr><td colspan="7" class="text-center">Cargando...</td></tr>');

        $.get(`/leads/${leadId}/logs`, function (data) {
            if (data.length === 0) {
                $('#logsTableBody').html('<tr><td colspan="7" class="text-center">Sin registros</td></tr>');
                return;
            }

            let rows = '';
            data.forEach(log => {
                const tipoBadge = getTipoBadge(log.tipo);
                const soportesButton = getSoportesButton(log.archivo_soporte);
                rows += `
                    <tr>
                        <td>${tipoBadge}</td>
                        <td>${log.estado_anterior}</td>
                        <td>${log.estado_nuevo}</td>
                        <td>${log.comentario}</td>
                        <td>${log.usuario}</td>
                        <td>${log.fecha}</td>
                        <td>${soportesButton}</td>
                    </tr>
                `;
            });

            $('#logsTableBody').html(rows);
        }).fail(() => {
            $('#logsTableBody').html('<tr><td colspan="7" class="text-danger text-center">Error al cargar los logs.</td></tr>');
        });
    });

    function getTipoBadge(tipo) {
        switch(tipo) {
            case 'Pipeline':
                return '<span class="badge bg-primary">Pipeline</span>';
            case 'Traige':
                return '<span class="badge bg-info">Triage</span>';
            case 'Llamadas Traige':
                return '<span class="badge bg-success">Llamadas</span>';
            case 'Onboarding':
                return '<span class="badge bg-info">Onboarding</span>';
            case 'Upsell':
                return '<span class="badge bg-success">Upsell</span>';
            case 'Venta':
                return '<span class="badge bg-warning">Venta</span>';
            case 'Contrato':
                return '<span class="badge bg-dark">Contrato</span>';
            default:
                return '<span class="badge bg-secondary">General</span>';
        }
    }

    function getSoportesButton(archivoSoporte) {
        if (!archivoSoporte) {
            return '<span class="text-muted">-</span>';
        }

        // Si es un array de comprobantes (venta con multiples archivos)
        if (Array.isArray(archivoSoporte)) {
            if (archivoSoporte.length === 0) {
                return '<span class="text-muted">-</span>';
            }

            let buttons = '<div class="d-flex flex-wrap gap-1">';
            archivoSoporte.forEach((archivo, index) => {
                const url = archivo.url || archivo;
                const name = archivo.name || `Archivo ${index + 1}`;
                buttons += `<a href="${url}" target="_blank" class="btn btn-outline-primary btn-sm" title="${name}">
                    <i class="bi bi-download"></i> ${archivoSoporte.length > 1 ? (index + 1) : 'Descargar'}
                </a>`;
            });
            buttons += '</div>';
            return buttons;
        }

        // Si es un solo archivo (string URL)
        return `<a href="${archivoSoporte}" target="_blank" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-download"></i> Descargar
                </a>`;
    }

    $(document).on('click', '.view-sale-btn', function () {
        // Información del Cliente
        $('#modal-nombre').text($(this).data('nombre') || 'N/A');
        $('#modal-apellido').text($(this).data('apellido') || 'N/A');
        $('#modal-email').text($(this).data('email') || 'N/A');
        $('#modal-telefono').text($(this).data('telefono') || 'N/A');
        $('#modal-identificacion').text($(this).data('identificacion') || 'N/A');
        $('#modal-fuente').text($(this).data('fuente') || 'N/A');

        // Información de Pago
        $('#modal-metodo-pago').text($(this).data('metodo_pago') || 'N/A');
        $('#modal-tipo-acuerdo').text($(this).data('tipo_acuerdo') || 'N/A');
        $('#modal-tipo-contrato').text($(this).data('tipo_contrato') || 'N/A');
        $('#modal-fecha-cierre').text($(this).data('fecha_cierre') || 'N/A');

        // Información del Contrato
        $('#modal-contrato').text($(this).data('contrato') || 'N/A');
        $('#modal-contrato-estado').text($(this).data('contrato_estado') || 'N/A');
        $('#modal-forma-pago').text($(this).data('forma_pago') || 'N/A');
        $('#modal-fecha-firma').text($(this).data('fecha_firma') || 'N/A');

        // Información Financiera
        const moneda = $(this).data('moneda') || 'USD';
        $('#modal-monto-total').text('$' + ($(this).data('monto_total') || '0.00') + ' ' + moneda);
        $('#modal-monto-final').text('$' + ($(this).data('monto_final') || '0.00') + ' ' + moneda);
        $('#modal-descuento').text($(this).data('descuento') || 'Sin descuento');
        $('#modal-total-pagado').text('$' + ($(this).data('total_pagado') || '0.00') + ' ' + moneda);

        // Estado de Pago con badge
        const estadoPago = $(this).data('estado_pago') || 'N/A';
        const estadoPagoBadge = $(this).data('estado_pago_badge') || 'secondary';
        $('#modal-estado-pago').html('<span class="badge bg-' + estadoPagoBadge + '">' + estadoPago + '</span>');

        // Estado de Cuotas
        const cuotas = $(this).data('cuotas') || [];
        const cuotasContainer = $('#modal-cuotas-container');
        cuotasContainer.empty();

        if (cuotas.length > 0) {
            let cuotasHtml = '<div class="table-responsive"><table class="table table-sm table-bordered">';
            cuotasHtml += '<thead class="table-light"><tr>';
            cuotasHtml += '<th>Cuota</th><th>Monto</th><th>Vencimiento</th><th>Estado</th><th>Pagado</th><th>Fecha Pago</th>';
            cuotasHtml += '</tr></thead><tbody>';

            cuotas.forEach(function(cuota) {
                cuotasHtml += '<tr>';
                cuotasHtml += '<td class="text-center">' + cuota.numero + '</td>';
                cuotasHtml += '<td class="text-end">$' + cuota.monto + ' ' + cuota.moneda + '</td>';
                cuotasHtml += '<td class="text-center">' + cuota.fecha_vencimiento + '</td>';
                cuotasHtml += '<td class="text-center"><span class="badge bg-' + cuota.estado_badge + '">' + cuota.estado + '</span></td>';
                cuotasHtml += '<td class="text-end">$' + cuota.monto_pagado + '</td>';
                cuotasHtml += '<td class="text-center">' + (cuota.fecha_pago || '-') + '</td>';
                cuotasHtml += '</tr>';
            });

            cuotasHtml += '</tbody></table></div>';
            cuotasContainer.html(cuotasHtml);
        } else {
            cuotasContainer.html('<span class="text-muted">Sin cuotas registradas</span>');
        }

        // Comentarios
        $('#modal-comentarios').text($(this).data('comentarios') || 'Sin comentarios');

        // Comprobantes múltiples
        const comprobantes = $(this).data('comprobantes') || [];
        const comprobantesContainer = $('#modal-comprobantes-container');
        comprobantesContainer.empty();

        if (comprobantes.length > 0) {
            comprobantes.forEach(function(comp, index) {
                comprobantesContainer.append(`
                    <a href="${comp.url}" target="_blank" class="btn btn-outline-success btn-sm me-2 mb-2">
                        <i class="bi bi-download me-1"></i> ${comp.name || 'Comprobante ' + (index + 1)}
                    </a>
                `);
            });
        } else {
            comprobantesContainer.html('<span class="text-muted">Sin comprobantes</span>');
        }

        $('#saleInfoModal').modal('show');
    });

    document.addEventListener('DOMContentLoaded', function () {
        const table = $('#leads-table').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            scrollX: true,
            autoWidth: false,
            ajax: "{{ route('leads') }}",
            columns: [
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'noVis'
                },
                { 
                    data: 'pipeline_status', 
                    name: 'pipeline_status',
                    orderable: false, 
                    searchable: true
                },
                { data: 'nombre', name: 'nombre' },
                { data: 'email', name: 'email' },
                { data: 'telefono', name: 'telefono' },
                { data: 'instagram_user', name: 'instagram_user' }
            ],

            dom: "<'flex flex-wrap justify-between items-center mb-4'<'relative'B>f>" + 
                 "t" + 
                 "<'flex justify-between items-center px-2 my-2'i<'pagination-wrapper'p>>",

            buttons: [
                {
                    extend: 'pageLength',
                    className: 'btn btn-outline-dark',
                    text: 'Filas '
                },
                {
                    extend: 'colvis',
                    text: 'Columnas',
                    columns: ':not(.noVis)',
                    className: 'btn btn-outline-dark'
                },
                {
                    extend: 'excelHtml5',
                    text: 'Excel',
                    className: 'btn btn-outline-success'
                }
            ],
            language: {
                url: '{{ asset("js/datatables/es-ES.json") }}',
                buttons: {
                    pageLength: {
                        _: "Mostrar %d filas",
                        '-1': "Mostrar todos"
                    }
                }
            },
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]]
        });

        // Configuración de botones
        table.on('buttons-action', function () {
            setTimeout(() => {
                $('.dt-button-collection')
                    .addClass('bg-white border border-gray-300 rounded shadow-md mt-2 p-2')
                    .css({
                        position: 'absolute',
                        'z-index': 999,
                        top: 'calc(100% + 0.5rem)',
                        left: '0',
                        right: 'auto'
                    });

                $('.dt-button-collection button')
                    .removeClass()
                    .addClass('block w-full text-left text-sm text-gray-800 px-4 py-2 rounded hover:bg-gray-100 cursor-pointer transition-colors duration-150');
            }, 50);
        });

        // Variables para manejar el estado del pipeline
        let originalStatusId;

        $('#leads-table').on('focus', '.pipeline-status-select', function () {
            originalStatusId = $(this).val();
        });

        $('#leads-table').on('change', '.pipeline-status-select', function () {
            const leadId = $(this).data('lead-id');
            const statusId = $(this).val();
            const selectElement = $(this);

            $('#comentarioModal #leadId').val(leadId);
            $('#comentarioModal #statusId').val(statusId);
            $('#comentarioModal').modal('show');
            
            $('#comentarioModal').off('hidden.bs.modal').on('hidden.bs.modal', function () {
                if (!$('#comentarioForm').data('submitted')) {
                    selectElement.val(originalStatusId);
                }
                $('#comentarioForm').data('submitted', false);
                $('#comentarioText').val('');
            });
        });

        $('#guardarComentario').on('click', function() {
            const leadId = $('#comentarioModal #leadId').val();
            const statusId = $('#comentarioModal #statusId').val();
            const comentario = $('#comentarioModal #comentarioText').val();

            $.ajax({
                url: `/leads/${leadId}/update-status`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    status_id: statusId,
                    comentario: comentario
                },
                success: function(response) {
                    $('#comentarioModal').modal('hide');
                    $('#comentarioForm').data('submitted', true);
                    $('#leads-table').DataTable().ajax.reload(null, false);
                },
                error: function(xhr) {
                    alert('Error al actualizar el estado.');
                }
            });
        });

        // Manejar descarga de contratos
        $(document).on('click', '.download-contract-btn', function() {
            const saleId = $(this).data('sale-id');
            window.open(`{{ route('contracts.download', ':id') }}`.replace(':id', saleId), '_blank');
        });

        // Manejar reenvío de email de contrato
        $(document).on('click', '.resend-contract-btn', function() {
            const saleId = $(this).data('sale-id');
            const button = $(this);

            if (confirm('¿Está seguro de que desea reenviar el email del contrato?')) {
                button.prop('disabled', true);

                $.ajax({
                    url: `{{ route('contracts.resend', ':id') }}`.replace(':id', saleId),
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Email de contrato reenviado exitosamente.');
                            // Opcional: mostrar la URL en consola para testing
                            console.log('URL del contrato:', response.contract_url);
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr) {
                        alert('Error al reenviar el email del contrato.');
                    },
                    complete: function() {
                        button.prop('disabled', false);
                    }
                });
            }
        });

        // ===================================================
        // CLOSER DAILY FORM - TOP OF PAGE
        // ===================================================
        let closerDailyFormData = null;
        let closerDailyFormLoaded = false;
        let hasSubmittedToday = false;

        // Load Closer Daily status and form on page load
        function loadCloserDailyForm() {
            $.get('/forms/closer-daily/render', function(response) {
                if (response.success) {
                    closerDailyFormData = response.form;
                    hasSubmittedToday = response.has_submitted_today;

                    // Check if already submitted today
                    if (response.has_submitted_today) {
                        // Show info alert
                        $('#closerDailyInfoAlert').removeClass('d-none');
                        $('#closerDailyInfoMessage').text(
                            `Ya completaste el formulario Closer Daily hoy a las ${response.today_submission_time}. Puedes volver a llenarlo si lo deseas.`
                        );

                        // Show "Completado" badge
                        $('#closerDailyStatusBadge').html(
                            `<span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Completado hoy a las ${response.today_submission_time}
                            </span>`
                        );

                        // Hide the "Llenar" button, keep form collapsed
                        $('#closerDailyShowFormContainer').addClass('d-none');
                        $('#closerDailyFormContainer').removeClass('show');
                    } else {
                        // Not submitted today - show form button
                        $('#closerDailyInfoAlert').addClass('d-none');
                        $('#closerDailyShowFormContainer').removeClass('d-none');

                        // Show "Pendiente" badge
                        $('#closerDailyStatusBadge').html(
                            `<span class="badge bg-warning text-dark">
                                <i class="bi bi-exclamation-circle"></i> Pendiente
                            </span>`
                        );
                    }

                    // Render form fields (but keep collapsed)
                    renderCloserFormFields(response.form.fields);
                    closerDailyFormLoaded = true;
                } else {
                    $('#closerDailyFieldsContainer').html(
                        '<p class="text-danger text-center">Error al cargar el formulario.</p>'
                    );
                    $('#closerDailyStatusBadge').html(
                        '<span class="badge bg-danger">Error</span>'
                    );
                }
            }).fail(function() {
                $('#closerDailyFieldsContainer').html(
                    '<p class="text-danger text-center">Error al cargar el formulario.</p>'
                );
                $('#closerDailyStatusBadge').html(
                    '<span class="badge bg-danger">Error</span>'
                );
            });
        }

        // Open form button click
        $('#openCloserDailyFormBtn, #showCloserDailyFormBtn').on('click', function() {
            $('#closerDailyFormContainer').collapse('show');
            $('#closerDailyShowFormContainer').addClass('d-none');
            $('#closerDailyInfoAlert').addClass('d-none');
        });

        // Cancel button click
        $('#cancelCloserDailyForm').on('click', function() {
            $('#closerDailyFormContainer').collapse('hide');

            // Show appropriate UI based on submission status
            if (hasSubmittedToday) {
                // Already submitted today
                $('#closerDailyInfoAlert').removeClass('d-none');
            } else {
                // Not submitted today
                $('#closerDailyShowFormContainer').removeClass('d-none');
            }

            // Reset form
            $('#closerDailyForm')[0].reset();
            // Reset star ratings
            $('.star').css('color', '#ccc');
        });

        // Render form fields
        function renderCloserFormFields(fields) {
            let html = '';

            fields.forEach(field => {
                html += `<div class="mb-3">`;
                html += `<label for="field_${field.field_name}" class="form-label">${field.label}`;
                if (field.is_required) {
                    html += ' <span class="text-danger">*</span>';
                }
                html += `</label>`;

                // Generate field by type
                switch(field.field_type) {
                    case 'text':
                    case 'email':
                        html += `<input type="${field.field_type}" class="form-control" id="field_${field.field_name}" name="${field.field_name}"
                                placeholder="${field.placeholder || ''}" ${field.is_required ? 'required' : ''}>`;
                        break;

                    case 'textarea':
                        html += `<textarea class="form-control" id="field_${field.field_name}" name="${field.field_name}" rows="3"
                                placeholder="${field.placeholder || ''}" ${field.is_required ? 'required' : ''}></textarea>`;
                        break;

                    case 'number':
                        html += `<input type="number" class="form-control" id="field_${field.field_name}" name="${field.field_name}"
                                placeholder="${field.placeholder || ''}" ${field.is_required ? 'required' : ''}>`;
                        break;

                    case 'date':
                        html += `<input type="date" class="form-control" id="field_${field.field_name}" name="${field.field_name}"
                                ${field.is_required ? 'required' : ''}>`;
                        break;

                    case 'select':
                        html += `<select class="form-select" id="field_${field.field_name}" name="${field.field_name}" ${field.is_required ? 'required' : ''}>`;
                        html += `<option value="">Seleccione una opcion</option>`;
                        if (field.options && Array.isArray(field.options)) {
                            field.options.forEach(opt => {
                                html += `<option value="${opt}">${opt}</option>`;
                            });
                        }
                        html += `</select>`;
                        break;

                    case 'radio':
                        if (field.options && Array.isArray(field.options)) {
                            field.options.forEach(opt => {
                                html += `<div class="form-check">
                                    <input class="form-check-input" type="radio" name="${field.field_name}" id="field_${field.field_name}_${opt}" value="${opt}" ${field.is_required ? 'required' : ''}>
                                    <label class="form-check-label" for="field_${field.field_name}_${opt}">${opt}</label>
                                </div>`;
                            });
                        }
                        break;

                    case 'checkbox':
                        if (field.options && Array.isArray(field.options)) {
                            field.options.forEach(opt => {
                                html += `<div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="${field.field_name}[]" id="field_${field.field_name}_${opt}" value="${opt}">
                                    <label class="form-check-label" for="field_${field.field_name}_${opt}">${opt}</label>
                                </div>`;
                            });
                        }
                        break;

                    case 'scale':
                        const min = field.options?.min || 1;
                        const max = field.options?.max || 10;
                        html += `<div class="d-flex gap-2 align-items-center">`;
                        for (let i = min; i <= max; i++) {
                            html += `<div class="form-check">
                                <input class="form-check-input" type="radio" name="${field.field_name}" id="field_${field.field_name}_${i}" value="${i}" ${field.is_required ? 'required' : ''}>
                                <label class="form-check-label" for="field_${field.field_name}_${i}">${i}</label>
                            </div>`;
                        }
                        html += `</div>`;
                        break;

                    case 'rating':
                        const stars = field.options?.max || 5;
                        html += `<div class="star-rating d-flex gap-2">`;
                        for (let i = 1; i <= stars; i++) {
                            html += `<span class="star" data-field="${field.field_name}" data-value="${i}" style="font-size: 24px; cursor: pointer; color: #ccc;">&#9733;</span>`;
                        }
                        html += `</div>`;
                        html += `<input type="hidden" name="${field.field_name}" id="field_${field.field_name}" ${field.is_required ? 'required' : ''}>`;
                        break;
                }

                if (field.help_text) {
                    html += `<small class="form-text text-muted">${field.help_text}</small>`;
                }

                html += `</div>`;
            });

            $('#closerDailyFieldsContainer').html(html);

            // Activate star rating functionality
            $('#closerDailyFieldsContainer').on('click', '.star', function() {
                const fieldName = $(this).data('field');
                const value = $(this).data('value');
                $(`input[name="${fieldName}"]`).val(value);

                // Color stars
                $(this).parent().find('.star').each(function(index) {
                    if (index < value) {
                        $(this).css('color', '#ffc107');
                    } else {
                        $(this).css('color', '#ccc');
                    }
                });
            });
        }

        // Submit form
        $('#closerDailyForm').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            // Validate required fields
            if (!this.checkValidity()) {
                this.reportValidity();
                return;
            }

            // Disable submit button
            $('#submitCloserDailyForm').prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm"></span> Enviando...'
            );

            $.ajax({
                url: '/forms/closer-daily/submit',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Formulario Enviado',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 2000
                        }).then(() => {
                            // Reload the form status
                            loadCloserDailyForm();

                            // Collapse the form
                            $('#closerDailyFormContainer').collapse('hide');
                            $('#closerDailyForm')[0].reset();
                            $('.star').css('color', '#ccc');
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Error al enviar el formulario.';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        errorMessage = Object.values(xhr.responseJSON.errors).flat().join('\n');
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage
                    });
                    console.error(xhr.responseText);
                },
                complete: function() {
                    $('#submitCloserDailyForm').prop('disabled', false).html(
                        '<i class="bi bi-send"></i> Enviar Formulario'
                    );
                }
            });
        });

        // Load form on page ready
        loadCloserDailyForm();
    });
</script>
@endpush

</x-app-layout>