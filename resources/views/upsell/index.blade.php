<x-app-layout>
    <x-slot name="header">
        {{ __('Upsells') }}
    </x-slot>

    <div class="py-12" style="padding-top: 0;">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="text-2xl font-semibold">Gestión de Upsells</h4>

                        <!-- Filtros -->
                        <div class="d-flex gap-2">
                            <select id="statusFilter" class="form-select form-select-sm">
                                <option value="">Todos los estados</option>
                                <option value="pendiente">Pendientes</option>
                                <option value="aprobado">Aprobados</option>
                            </select>
                        </div>
                    </div>

                    <div class="border border-gray-300 rounded-lg">
                        <div class="overflow-x-auto">
                            <table id="upsells-table" class="table-responsive w-full text-sm text-left text-gray-700">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                    <tr class="border-b border-gray-300">
                                        <th class="px-6 py-3" data-priority="1">Acciones</th>
                                        <th class="px-6 py-3">Lead</th>
                                        <th class="px-6 py-3">Estado Upsell</th>
                                        <th class="px-6 py-3">Fecha Pendiente</th>
                                        <th class="px-6 py-3">Fecha Aprobado</th>
                                        <th class="px-6 py-3">Usuario Pendiente</th>
                                        <th class="px-6 py-3">Usuario Aprobado</th>
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

    <!-- Usar componente reutilizable para modal de venta -->
    <x-sale-details-modal />

    <!-- Modal para aprobar upsell -->
    <div class="modal fade" id="approveUpsellModal" tabindex="-1" aria-labelledby="approveUpsellModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approveUpsellModalLabel">Aprobar Upsell - Pasar a High Ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="approveUpsellForm" enctype="multipart/form-data">
                        <input type="hidden" id="approveSaleId" name="sale_id">

                        <div class="mb-3">
                            <label for="leadInfoApprove" class="form-label">Lead</label>
                            <input type="text" id="leadInfoApprove" class="form-control" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="comprobanteUpsell" class="form-label">Comprobante de Pago Upsell <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="comprobanteUpsell" name="comprobante_upsell" required
                                   accept=".jpg,.jpeg,.png,.pdf">
                            <div class="form-text">Formatos permitidos: JPG, PNG, PDF. Máximo 2MB.</div>
                        </div>

                        <div class="mb-3">
                            <label for="comentariosAprobacion" class="form-label">Comentarios de Aprobación</label>
                            <textarea class="form-control" id="comentariosAprobacion" name="comentarios_aprobacion" rows="3"
                                      placeholder="Comentarios adicionales sobre la aprobación..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="confirmApproveUpsell">Aprobar y Pasar a High Ticket</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para historial de upsell -->
    <div class="modal fade" id="upsellLogsModal" tabindex="-1" aria-labelledby="upsellLogsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Historial de Upsell</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Tipo</th>
                                <th>Estado Anterior</th>
                                <th>Estado Nuevo</th>
                                <th>Comentario</th>
                                <th>Usuario</th>
                                <th>Fecha</th>
                                <th>Soportes</th>
                            </tr>
                        </thead>
                        <tbody id="upsellLogsTableBody">
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
        const table = $('#upsells-table').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            scrollX: true,
            autoWidth: false,
            ajax: {
                url: "{{ route('upsell.index') }}",
                data: function (d) {
                    d.status = $('#statusFilter').val();
                }
            },
            columns: [
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'noVis'
                },
                {
                    data: 'lead_info',
                    name: 'lead_info',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'upsell_status',
                    name: 'upsell_status',
                    orderable: false,
                    searchable: false
                },
                { data: 'fecha_pendiente', name: 'fecha_pendiente' },
                { data: 'fecha_aprobado', name: 'fecha_aprobado' },
                { data: 'user_pendiente', name: 'user_pendiente' },
                { data: 'user_aprobado', name: 'user_aprobado' }
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

        // Filtro por estado
        $('#statusFilter').on('change', function() {
            table.ajax.reload();
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

        // Aprobar upsell
        $(document).on('click', '.approve-upsell-btn', function() {
            const saleId = $(this).data('sale-id');
            const leadName = $(this).data('lead-name');

            $('#approveSaleId').val(saleId);
            $('#leadInfoApprove').val(leadName);
            $('#approveUpsellForm')[0].reset();
            $('#approveSaleId').val(saleId);
            $('#leadInfoApprove').val(leadName);

            $('#approveUpsellModal').modal('show');
        });

        // Confirmar aprobación de upsell
        $('#confirmApproveUpsell').on('click', function() {
            const button = $(this);
            const originalText = button.text();
            const saleId = $('#approveSaleId').val();

            // Deshabilitar botón y mostrar loading
            button.prop('disabled', true);
            button.html('<span class="spinner-border spinner-border-sm me-2" role="status"></span>Procesando...');

            const formData = new FormData($('#approveUpsellForm')[0]);

            $.ajax({
                url: `/upsell/${saleId}/approve`,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#approveUpsellModal').modal('hide');
                    table.ajax.reload(null, false);

                    Swal.fire({
                        title: '¡Éxito!',
                        text: 'Upsell aprobado correctamente. El lead ha sido cambiado a High Ticket.',
                        icon: 'success',
                        confirmButtonText: 'Aceptar'
                    });
                },
                error: function(xhr) {
                    let errorMsg = 'Error al aprobar el upsell.';
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

        // Ver historial de upsell
        $(document).on('click', '.view-upsell-logs-btn', function () {
            const saleId = $(this).data('sale-id');
            $('#upsellLogsModal').modal('show');
            $('#upsellLogsTableBody').html('<tr><td colspan="7" class="text-center">Cargando...</td></tr>');

            $.get(`/upsell/${saleId}/logs`, function (data) {
                if (data.length === 0) {
                    $('#upsellLogsTableBody').html('<tr><td colspan="7" class="text-center">Sin registros</td></tr>');
                    return;
                }

                let rows = '';
                data.forEach(log => {
                    const tipoBadge = getTipoBadge(log.tipo);
                    const soportesButton = getSoportesButton(log.archivo_soporte);
                    rows += `
                        <tr>
                            <td>${tipoBadge}</td>
                            <td>${log.valor_viejo}</td>
                            <td>${log.valor_nuevo}</td>
                            <td>${log.detalle}</td>
                            <td>${log.usuario}</td>
                            <td>${log.fecha}</td>
                            <td>${soportesButton}</td>
                        </tr>
                    `;
                });

                $('#upsellLogsTableBody').html(rows);
            }).fail(() => {
                $('#upsellLogsTableBody').html('<tr><td colspan="7" class="text-danger text-center">Error al cargar los logs.</td></tr>');
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