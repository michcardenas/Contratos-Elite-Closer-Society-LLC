<x-app-layout>
    <x-slot name="header">
        {{ __('Leads con Ventas Cerradas - Onboarding') }}
    </x-slot>

    <div class="py-12" style="padding-top: 0;">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h4 class="text-2xl font-semibold mb-4">Leads para Onboarding</h4>

                    <div class="border border-gray-300 rounded-lg">
                        <div class="overflow-x-auto">
                            <table id="onboarding-leads-table" class="table-responsive w-full text-sm text-left text-gray-700">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                    <tr class="border-b border-gray-300">
                                        <th class="px-6 py-3" data-priority="1">Acciones</th>
                                        <th class="px-6 py-3">Estado Onboarding</th>
                                        <th class="px-6 py-3">Estado Pago</th>
                                        <th class="px-6 py-3">Próxima Llamada</th>
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

    <!-- Modal para programar llamada -->
    <div class="modal fade" id="scheduleCallModal" tabindex="-1" aria-labelledby="scheduleCallModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="scheduleCallModalLabel">Programar Llamada de Onboarding</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="scheduleCallForm">
                        <input type="hidden" id="leadId" name="lead_id">
                        <input type="hidden" id="parentCallId" name="parent_call_id">
                        
                        <div class="mb-3">
                            <label for="leadInfo" class="form-label">Lead</label>
                            <input type="text" id="leadInfo" class="form-control" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label for="scheduledDate" class="form-label">Fecha y Hora <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="scheduledDate" name="scheduled_date" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="callLink" class="form-label">Link de la Llamada <span class="text-danger">*</span></label>
                            <input type="url" class="form-control" id="callLink" name="call_link" required 
                                   placeholder="https://meet.google.com/abc-defg-hij">
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notas</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                      placeholder="Notas adicionales para la llamada..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="saveScheduleCall">Programar Llamada</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Usar componente reutilizable para modal de venta -->
    <x-sale-details-modal />

    <!-- Modal para trazabilidad de llamadas -->
    <div class="modal fade" id="callsTraceModal" tabindex="-1" aria-labelledby="callsTraceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Trazabilidad de Llamadas de Onboarding</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div id="callsTraceContent">
                        <div class="text-center">Cargando...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para historial de una llamada específica -->
    <div class="modal fade" id="callLogsModal" tabindex="-1" aria-labelledby="callLogsModalLabel" aria-hidden="true">
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
                                <th>Estado Anterior</th>
                                <th>Estado Nuevo</th>
                                <th>Comentario</th>
                                <th>Usuario</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody id="callLogsTableBody">
                            <tr><td colspan="5" class="text-center">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para gestionar notas del lead -->
    <div class="modal fade" id="leadNotesModal" tabindex="-1" aria-labelledby="leadNotesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Notas del Lead - <span id="leadNotesTitle"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- Panel para agregar nueva nota -->
                        <div class="col-md-4">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="bi bi-plus-circle"></i> Agregar Nueva Nota</h6>
                                </div>
                                <div class="card-body">
                                    <form id="addNoteForm">
                                        <input type="hidden" id="noteLeadId" name="lead_id">
                                        <div class="mb-3">
                                            <label for="newNote" class="form-label">Nota <span class="text-danger">*</span></label>
                                            <textarea class="form-control" id="newNote" name="note" rows="4" 
                                                      placeholder="Escribe aquí tu nota sobre el lead..."></textarea>
                                            <div class="form-text">Mínimo 5 caracteres, máximo 2000.</div>
                                        </div>
                                        <button type="submit" class="btn btn-success btn-sm w-100" id="saveNoteBtn">
                                            <i class="bi bi-check-lg"></i> Guardar Nota
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Panel para mostrar historial de notas -->
                        <div class="col-md-8">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="bi bi-clock-history"></i> Historial de Notas</h6>
                                </div>
                                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                    <div id="notesHistoryContent">
                                        <div class="text-center">Cargando notas...</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para historial de cambios de pipeline -->
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
<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function () {
        // Inicializar DataTable
        const table = $('#onboarding-leads-table').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            scrollX: true,
            autoWidth: false,
            ajax: "{{ route('onboarding.leads') }}",
            columns: [
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'noVis'
                },
                {
                    data: 'onboarding_status',
                    name: 'onboarding_status',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'payment_status',
                    name: 'payment_status',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'next_call',
                    name: 'next_call',
                    orderable: false,
                    searchable: false
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

        // Programar llamada
        $(document).on('click', '.schedule-call-btn', function () {
            const leadId = $(this).data('lead-id');
            const leadName = $(this).data('lead-name');
            const leadEmail = $(this).data('lead-email');
            
            $('#leadId').val(leadId);
            $('#parentCallId').val(''); // Reset parent call
            $('#leadInfo').val(`${leadName} (${leadEmail})`);
            $('#scheduleCallForm')[0].reset();
            $('#leadId').val(leadId); // Restore after reset
            $('#leadInfo').val(`${leadName} (${leadEmail})`); // Restore after reset
            
            // Set minimum date to now
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            $('#scheduledDate').attr('min', now.toISOString().slice(0, 16));
            
            $('#scheduleCallModal').modal('show');
        });

        // Guardar programación de llamada
        $('#saveScheduleCall').on('click', function() {
            const button = $(this);
            const originalText = button.text();
            
            // Deshabilitar botón y mostrar loading
            button.prop('disabled', true);
            button.html('<span class="spinner-border spinner-border-sm me-2" role="status"></span>Programando...');
            
            const formData = new FormData($('#scheduleCallForm')[0]);
            
            $.ajax({
                url: '{{ route("onboarding.calls.store") }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    // Cerrar modal inmediatamente
                    $('#scheduleCallModal').modal('hide');
                    table.ajax.reload(null, false);
                    
                    // Mostrar alerta de éxito
                    Swal.fire({
                        title: '¡Éxito!',
                        text: 'Llamada programada correctamente y email enviado al lead.',
                        icon: 'success',
                        confirmButtonText: 'Aceptar'
                    });
                },
                error: function(xhr) {
                    let errorMsg = 'Error al programar la llamada.';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMsg = errors.join('<br>');
                    }
                    Swal.fire({
                        title: 'Error',
                        html: errorMsg,
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                },
                complete: function() {
                    // Restaurar botón original
                    button.prop('disabled', false);
                    button.html(originalText);
                }
            });
        });

        // Ver información de venta
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

        // Ver trazabilidad de llamadas
        $(document).on('click', '.view-calls-btn', function () {
            const leadId = $(this).data('lead-id');
            $('#callsTraceContent').html('<div class="text-center">Cargando...</div>');
            $('#callsTraceModal').modal('show');
            
            $.get(`{{ url('/onboarding/leads') }}/${leadId}/calls`, function (data) {
                if (data.length === 0) {
                    $('#callsTraceContent').html('<div class="text-center">No hay llamadas programadas para este lead.</div>');
                    return;
                }

                let content = '<div class="table-responsive"><table class="table table-bordered">';
                content += '<thead class="table-light">';
                content += '<tr><th>Fecha Programada</th><th>Estado</th><th>Link</th><th>Notas</th><th>Comentarios</th><th>Usuario CMS</th><th>Email Enviado</th><th>Acciones</th></tr>';
                content += '</thead><tbody>';

                data.forEach(call => {
                    const statusBadge = getStatusBadge(call.status);
                    const emailBadge = call.email_sent ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-danger">No</span>';
                    
                    content += `<tr>
                        <td>${call.scheduled_date}</td>
                        <td>${statusBadge}</td>
                        <td><a href="${call.call_link}" target="_blank" class="btn btn-sm btn-outline-primary">Unirse</a></td>
                        <td>${call.notes || '-'}</td>
                        <td>${call.comments || '-'}</td>
                        <td>${call.user_name}</td>
                        <td>${emailBadge}</td>
                        <td>
                            <div class="btn-group-vertical gap-1">
                                <button class="btn btn-sm btn-outline-info update-call-status-btn" 
                                    data-call-id="${call.id}" 
                                    data-current-status="${call.status}">
                                    Actualizar Estado
                                </button>
                                <button class="btn btn-sm btn-outline-secondary view-call-logs-btn" 
                                    data-call-id="${call.id}">
                                    Ver Historial
                                </button>
                            </div>
                            ${call.status === 'pendiente' || call.status === 'no_realizada' ? 
                                `<button class="btn btn-sm btn-outline-warning reschedule-call-btn mt-2" 
                                    data-call-id="${call.id}" 
                                    data-lead-id="${leadId}">
                                    <i class="bi bi-arrow-clockwise"></i> Reprogramar
                                </button>` : ''}
                        </td>
                    </tr>`;
                });

                content += '</tbody></table></div>';
                $('#callsTraceContent').html(content);
            }).fail(() => {
                $('#callsTraceContent').html('<div class="text-danger text-center">Error al cargar las llamadas.</div>');
            });
        });

        // Ver logs de una llamada específica
        $(document).on('click', '.view-call-logs-btn', function () {
            const callId = $(this).data('call-id');
            $('#callLogsTableBody').html('<tr><td colspan="5" class="text-center">Cargando...</td></tr>');
            $('#callLogsModal').modal('show');
            
            $.get(`{{ url('/onboarding/calls') }}/${callId}/logs`, function (data) {
                if (data.length === 0) {
                    $('#callLogsTableBody').html('<tr><td colspan="5" class="text-center">Sin registros de cambios</td></tr>');
                    return;
                }

                let rows = '';
                data.forEach(log => {
                    rows += `
                        <tr>
                            <td>${log.valor_viejo || 'N/A'}</td>
                            <td>${log.valor_nuevo}</td>
                            <td>${log.detalle}</td>
                            <td>${log.usuario}</td>
                            <td>${log.fecha}</td>
                        </tr>
                    `;
                });

                $('#callLogsTableBody').html(rows);
            }).fail(() => {
                $('#callLogsTableBody').html('<tr><td colspan="5" class="text-danger text-center">Error al cargar los logs.</td></tr>');
            });
        });

        function getStatusBadge(status) {
            const badges = {
                'pendiente': '<span class="badge bg-warning">Pendiente</span>',
                'realizada': '<span class="badge bg-success">Realizada</span>',
                'no_realizada': '<span class="badge bg-danger">No Realizada</span>',
                'reprogramada': '<span class="badge bg-info">Reprogramada</span>'
            };
            return badges[status] || '<span class="badge bg-secondary">Desconocido</span>';
        }

        // Actualizar estado de llamada
        $(document).on('click', '.update-call-status-btn', function () {
            const callId = $(this).data('call-id');
            const currentStatus = $(this).data('current-status');
            
            const statusOptions = {
                'pendiente': 'Pendiente',
                'realizada': 'Realizada', 
                'no_realizada': 'No Realizada'
            };
            
            let optionsHtml = '';
            Object.keys(statusOptions).forEach(status => {
                const selected = status === currentStatus ? 'selected' : '';
                optionsHtml += `<option value="${status}" ${selected}>${statusOptions[status]}</option>`;
            });
            
            const html = `
                <div class="mb-3">
                    <label class="form-label">Estado de la Llamada</label>
                    <select class="form-select" id="callStatus">
                        ${optionsHtml}
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Comentarios</label>
                    <textarea class="form-control" id="callComments" rows="3" placeholder="Comentarios sobre la llamada..."></textarea>
                </div>
            `;
            
            // Crear modal dinámico
            const modalHtml = `
                <div class="modal fade" id="updateStatusModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Actualizar Estado de Llamada</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                ${html}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-primary" id="confirmUpdateStatus">Actualizar</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $('body').append(modalHtml);
            $('#updateStatusModal').modal('show');
            
            $('#updateStatusModal').on('hidden.bs.modal', function () {
                $(this).remove();
            });
            
            $('#confirmUpdateStatus').on('click', function() {
                const newStatus = $('#callStatus').val();
                const comments = $('#callComments').val();
                
                $.ajax({
                    url: `{{ url('/onboarding/calls') }}/${callId}/status`,
                    method: 'PUT',
                    data: {
                        status: newStatus,
                        comments: comments,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $('#updateStatusModal').modal('hide');
                        $('#callsTraceModal').modal('hide');
                        table.ajax.reload(null, false);
                        Swal.fire({
                            title: '¡Éxito!',
                            text: 'Estado actualizado correctamente.',
                            icon: 'success',
                            confirmButtonText: 'Aceptar'
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error',
                            text: 'Error al actualizar el estado.',
                            icon: 'error',
                            confirmButtonText: 'Aceptar'
                        });
                    }
                });
            });
        });

        // Reprogramar llamada
        $(document).on('click', '.reschedule-call-btn', function () {
            const callId = $(this).data('call-id');
            const leadId = $(this).data('lead-id');
            const button = $(this);
            
            // Deshabilitar botón y mostrar loading
            button.prop('disabled', true);
            button.html('<span class="spinner-border spinner-border-sm me-1" role="status"></span>Reprogramando...');
            
            // Marcar la llamada actual como reprogramada automáticamente
            $.ajax({
                url: `{{ url('/onboarding/calls') }}/${callId}/reschedule`,
                method: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    // Ahora abrir modal para nueva llamada
                    $('#parentCallId').val(callId);
                    $('#leadId').val(leadId);
                    
                    // Obtener info del lead
                    const leadBtn = $(`.schedule-call-btn[data-lead-id="${leadId}"]`);
                    const leadName = leadBtn.data('lead-name');
                    const leadEmail = leadBtn.data('lead-email');
                    $('#leadInfo').val(`${leadName} (${leadEmail}) - REPROGRAMADA`);
                    
                    $('#scheduleCallForm')[0].reset();
                    $('#leadId').val(leadId);
                    $('#parentCallId').val(callId);
                    $('#leadInfo').val(`${leadName} (${leadEmail}) - REPROGRAMADA`);
                    
                    // Set minimum date to now
                    const now = new Date();
                    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
                    $('#scheduledDate').attr('min', now.toISOString().slice(0, 16));
                    
                    $('#callsTraceModal').modal('hide');
                    $('#scheduleCallModal').modal('show');
                    
                    // Mostrar notificación de éxito
                    Swal.fire({
                        title: 'Reprogramada',
                        text: 'La llamada se marcó como reprogramada. Ahora programa la nueva fecha.',
                        icon: 'info',
                        timer: 3000,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Error al marcar como reprogramada.',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                },
                complete: function() {
                    // Restaurar botón
                    button.prop('disabled', false);
                    button.html('<i class="bi bi-arrow-clockwise"></i> Reprogramar');
                }
            });
        });

        // Gestionar notas del lead
        $(document).on('click', '.manage-notes-btn', function () {
            const leadId = $(this).data('lead-id');
            const leadName = $(this).data('lead-name');
            const leadEmail = $(this).data('lead-email');
            
            $('#noteLeadId').val(leadId);
            $('#leadNotesTitle').text(`${leadName} (${leadEmail})`);
            $('#newNote').val('');
            $('#notesHistoryContent').html('<div class="text-center">Cargando notas...</div>');
            
            $('#leadNotesModal').modal('show');
            
            // Cargar historial de notas
            loadLeadNotes(leadId);
        });

        // Función para cargar las notas del lead
        function loadLeadNotes(leadId) {
            $.get(`{{ url('/lead-notes') }}/${leadId}`, function (data) {
                if (data.notes.length === 0) {
                    $('#notesHistoryContent').html('<div class="text-center text-muted">No hay notas registradas para este lead.</div>');
                    return;
                }

                let content = '';
                data.notes.forEach(note => {
                    content += `
                        <div class="card mb-2 border-light">
                            <div class="card-header bg-light py-2">
                                <small class="text-muted">
                                    <i class="bi bi-person-circle"></i> ${note.user_name} • 
                                    <i class="bi bi-clock"></i> ${note.created_at} (${note.created_at_diff})
                                </small>
                            </div>
                            <div class="card-body py-2">
                                <p class="mb-1">${note.note.replace(/\n/g, '<br>')}</p>
                            </div>
                        </div>
                    `;
                });

                $('#notesHistoryContent').html(content);
            }).fail(function(xhr, status, error) {
                console.error('Error loading notes:', xhr.responseText);
                $('#notesHistoryContent').html('<div class="text-danger text-center">Error al cargar las notas: ' + (xhr.responseJSON?.message || xhr.statusText || error) + '</div>');
            });
        }

        // Guardar nueva nota
        $('#addNoteForm').on('submit', function(e) {
            e.preventDefault();
            
            const button = $('#saveNoteBtn');
            const originalText = button.html();
            
            // Deshabilitar botón y mostrar loading
            button.prop('disabled', true);
            button.html('<span class="spinner-border spinner-border-sm me-1" role="status"></span>Guardando...');
            
            const formData = new FormData(this);
            
            $.ajax({
                url: '{{ route("lead-notes.store") }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    // Limpiar formulario
                    $('#newNote').val('');
                    
                    // Recargar historial
                    const leadId = $('#noteLeadId').val();
                    loadLeadNotes(leadId);
                    
                    // Mostrar notificación de éxito
                    Swal.fire({
                        title: '¡Éxito!',
                        text: 'Nota agregada correctamente.',
                        icon: 'success',
                        timer: 2000,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    let errorMsg = 'Error al guardar la nota.';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMsg = errors.join('<br>');
                    }
                    Swal.fire({
                        title: 'Error',
                        html: errorMsg,
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                },
                complete: function() {
                    // Restaurar botón original
                    button.prop('disabled', false);
                    button.html(originalText);
                }
            });
        });

        // Manejar descarga de contratos
        $(document).on('click', '.download-contract-btn', function() {
            const saleId = $(this).data('sale-id');
            window.open(`{{ route('onboarding.contracts.download', ':id') }}`.replace(':id', saleId), '_blank');
        });

        // Manejar botón de Upsell
        $(document).on('click', '.upsell-btn', function() {
            const saleId = $(this).data('sale-id');
            const leadName = $(this).data('lead-name');

            const html = `
                <div class="mb-3">
                    <label class="form-label">Lead: ${leadName}</label>
                </div>
                <div class="mb-3">
                    <label class="form-label">Comentarios (opcional)</label>
                    <textarea class="form-control" id="upsellComments" rows="3" placeholder="Comentarios sobre el upsell..."></textarea>
                </div>
            `;

            Swal.fire({
                title: 'Pasar a Upsell',
                html: html,
                showCancelButton: true,
                confirmButtonText: 'Enviar a Upsell',
                cancelButtonText: 'Cancelar',
                preConfirm: () => {
                    return {
                        comments: document.getElementById('upsellComments').value
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/upsell/${saleId}/pendiente`,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            comentarios: result.value.comments
                        },
                        success: function(response) {
                            table.ajax.reload(null, false);
                            Swal.fire({
                                title: '¡Éxito!',
                                text: 'Lead enviado a Upsell correctamente.',
                                icon: 'success',
                                confirmButtonText: 'Aceptar'
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: 'Error',
                                text: 'Error al enviar a Upsell.',
                                icon: 'error',
                                confirmButtonText: 'Aceptar'
                            });
                        }
                    });
                }
            });
        });

        // Ver historial de cambios de pipeline
        $(document).on('click', '.view-logs-btn', function () {
            const leadId = $(this).data('lead-id');
            $('#logsModal').modal('show');
            $('#logsTableBody').html('<tr><td colspan="7" class="text-center">Cargando...</td></tr>');

            $.get(`{{ url('/onboarding/leads') }}/${leadId}/logs`, function (data) {
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
    });
</script>
@endpush

</x-app-layout>