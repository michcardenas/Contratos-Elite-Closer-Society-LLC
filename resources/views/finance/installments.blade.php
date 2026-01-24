<x-app-layout>
    <x-slot name="header">
        {{ __('Gestion de Cuotas') }}
    </x-slot>

    <div class="py-12" style="padding-top: 0;">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h4 class="text-2xl font-semibold mb-4">Cuotas</h4>

                    <!-- Filtros -->
                    <div class="mb-4">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-secondary filter-btn active" data-estado="">
                                Todas
                            </button>
                            <button type="button" class="btn btn-outline-warning filter-btn" data-estado="pendiente">
                                Pendientes
                            </button>
                            <button type="button" class="btn btn-outline-danger filter-btn" data-estado="vencida">
                                Vencidas
                            </button>
                            <button type="button" class="btn btn-outline-success filter-btn" data-estado="pagada">
                                Pagadas
                            </button>
                            <button type="button" class="btn btn-outline-info filter-btn" data-estado="parcial">
                                Pago Parcial
                            </button>
                        </div>
                    </div>

                    <div class="border border-gray-300 rounded-lg">
                        <div class="overflow-x-auto">
                            <table id="installments-table" class="table-responsive w-full text-sm text-left text-gray-700">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                    <tr class="border-b border-gray-300">
                                        <th class="px-6 py-3" data-priority="1">Acciones</th>
                                        <th class="px-6 py-3">Lead</th>
                                        <th class="px-6 py-3">Cliente</th>
                                        <th class="px-6 py-3">Tipo</th>
                                        <th class="px-6 py-3">Cuota</th>
                                        <th class="px-6 py-3">Monto</th>
                                        <th class="px-6 py-3">Vencimiento</th>
                                        <th class="px-6 py-3">Dias Vencido</th>
                                        <th class="px-6 py-3">Estado</th>
                                        <th class="px-6 py-3">Closer</th>
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

    <!-- Modal Registrar Pago -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Registrar Pago de Cuota</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="paymentForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="payment_installment_id">

                        <div class="alert alert-info mb-3">
                            <strong>Monto de la cuota:</strong> $<span id="cuota_monto_display">0.00</span>
                            <br>
                            <strong>Monto pendiente:</strong> $<span id="cuota_pendiente_display">0.00</span>
                        </div>

                        <div class="mb-3">
                            <label for="payment_monto" class="form-label fw-bold">Monto a Pagar *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="payment_monto" name="monto"
                                       step="0.01" min="0.01" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="payment_fecha" class="form-label fw-bold">Fecha de Pago *</label>
                            <input type="date" class="form-control" id="payment_fecha" name="fecha_pago" required>
                        </div>

                        <div class="mb-3">
                            <label for="payment_metodo" class="form-label fw-bold">Metodo de Pago *</label>
                            <select class="form-select" id="payment_metodo" name="metodo_pago" required>
                                <option value="">Seleccione un metodo</option>
                                <option value="D LOCAL GO">D Local Go</option>
                                <option value="WHOP">Whop</option>
                                <option value="TRANSFERENCIA USD">Transferencia USD</option>
                                <option value="TRANSFERENCIA PESOS">Transferencia Pesos</option>
                                <option value="CRIPTO">Criptomonedas</option>
                                <option value="EFECTIVO">Efectivo</option>
                                <option value="WIRE">Wire Transfer</option>
                                <option value="PAYPAL">PayPal</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="payment_comprobante" class="form-label">Comprobante</label>
                            <input type="file" class="form-control" id="payment_comprobante" name="comprobante"
                                   accept=".jpg,.jpeg,.png,.pdf">
                            <small class="text-muted">Formatos: JPG, PNG, PDF. Max 2MB.</small>
                        </div>

                        <div class="mb-3">
                            <label for="payment_referencia" class="form-label">Referencia</label>
                            <input type="text" class="form-control" id="payment_referencia" name="referencia"
                                   placeholder="Numero de transaccion, etc.">
                        </div>

                        <div class="mb-3">
                            <label for="payment_notas" class="form-label">Notas</label>
                            <textarea class="form-control" id="payment_notas" name="notas" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i> Registrar Pago
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Historial de Pagos -->
    <div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="historyModalLabel">Historial de Pagos - Cuota <span id="history_cuota_num"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-secondary mb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Monto de la cuota:</strong><br>
                                $<span id="history_monto">0.00</span> <span id="history_moneda">USD</span>
                            </div>
                            <div class="col-md-4">
                                <strong>Monto pagado:</strong><br>
                                $<span id="history_pagado">0.00</span>
                            </div>
                            <div class="col-md-4">
                                <strong>Monto pendiente:</strong><br>
                                $<span id="history_pendiente">0.00</span>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <strong>Fecha vencimiento:</strong> <span id="history_vencimiento">-</span>
                            </div>
                            <div class="col-md-6">
                                <strong>Estado:</strong> <span id="history_estado">-</span>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3">Pagos Registrados</h6>
                    <div id="payments_list">
                        <p class="text-muted">Cargando...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Obtener parametro de estado de la URL
    const urlParams = new URLSearchParams(window.location.search);
    const initialEstado = urlParams.get('estado') || '';

    // Activar boton de filtro segun parametro
    if (initialEstado) {
        document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
        const activeBtn = document.querySelector(`.filter-btn[data-estado="${initialEstado}"]`);
        if (activeBtn) activeBtn.classList.add('active');
    }

    const table = $('#installments-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        scrollX: true,
        autoWidth: false,
        ajax: {
            url: "{{ route('finanzas.cuotas') }}",
            data: function(d) {
                d.estado = document.querySelector('.filter-btn.active')?.dataset?.estado || '';
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
            { data: 'lead_name', name: 'lead_name' },
            { data: 'client_name', name: 'client_name' },
            { data: 'tipo_contrato', name: 'tipo_contrato' },
            { data: 'numero_cuota', name: 'numero_cuota' },
            { data: 'monto_formateado', name: 'monto' },
            { data: 'fecha_formateada', name: 'fecha_vencimiento' },
            { data: 'dias_vencido', name: 'dias_vencido', orderable: false },
            { data: 'estado_badge', name: 'estado' },
            { data: 'closer_name', name: 'closer_name' },
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

    // Filtros
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            table.ajax.reload();
        });
    });

    // Abrir modal de pago
    $(document).on('click', '.btn-register-payment', function() {
        const installmentId = $(this).data('installment-id');
        const monto = $(this).data('monto');
        const montoPendiente = $(this).data('monto-pendiente');

        $('#payment_installment_id').val(installmentId);
        $('#cuota_monto_display').text(parseFloat(monto).toFixed(2));
        $('#cuota_pendiente_display').text(parseFloat(montoPendiente).toFixed(2));
        $('#payment_monto').val(parseFloat(montoPendiente).toFixed(2));
        $('#payment_fecha').val(new Date().toISOString().split('T')[0]);
        $('#payment_metodo').val('');
        $('#payment_comprobante').val('');
        $('#payment_referencia').val('');
        $('#payment_notas').val('');

        $('#paymentModal').modal('show');
    });

    // Enviar formulario de pago
    $('#paymentForm').on('submit', function(e) {
        e.preventDefault();

        const installmentId = $('#payment_installment_id').val();
        const formData = new FormData(this);
        formData.append('_token', '{{ csrf_token() }}');

        $.ajax({
            url: `{{ url('finanzas/cuotas') }}/${installmentId}/pago`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#paymentModal').modal('hide');
                table.ajax.reload();
                Swal.fire({
                    icon: 'success',
                    title: 'Pago Registrado',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors;
                let errorMessage = 'Error al registrar el pago';

                if (errors) {
                    errorMessage = Object.values(errors).flat().join('\n');
                }

                Swal.fire('Error', errorMessage, 'error');
            }
        });
    });

    // Ver historial
    $(document).on('click', '.btn-view-history', function() {
        const installmentId = $(this).data('installment-id');

        $('#payments_list').html('<p class="text-muted">Cargando...</p>');
        $('#historyModal').modal('show');

        $.get(`{{ url('finanzas/cuotas') }}/${installmentId}/historial`, function(data) {
            $('#history_cuota_num').text(data.installment.numero_cuota);
            $('#history_monto').text(data.installment.monto);
            $('#history_moneda').text(data.installment.moneda);
            $('#history_pagado').text(data.installment.monto_pagado);
            $('#history_pendiente').text(data.installment.monto_pendiente);
            $('#history_vencimiento').text(data.installment.fecha_vencimiento);
            $('#history_estado').text(data.installment.estado);

            if (data.payments.length === 0) {
                $('#payments_list').html('<p class="text-muted">No hay pagos registrados.</p>');
                return;
            }

            let html = '<div class="table-responsive"><table class="table table-sm table-striped">';
            html += '<thead><tr><th>Fecha</th><th>Monto</th><th>Metodo</th><th>Referencia</th><th>Registrado por</th><th>Comprobante</th></tr></thead>';
            html += '<tbody>';

            data.payments.forEach(payment => {
                html += `<tr>
                    <td>${payment.fecha_pago}</td>
                    <td>$${payment.monto}</td>
                    <td>${payment.metodo_pago}</td>
                    <td>${payment.referencia || '-'}</td>
                    <td>${payment.registrado_por}</td>
                    <td>${payment.comprobante_url ? `<a href="${payment.comprobante_url}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-file-earmark"></i></a>` : '-'}</td>
                </tr>`;
            });

            html += '</tbody></table></div>';
            $('#payments_list').html(html);
        }).fail(function() {
            $('#payments_list').html('<p class="text-danger">Error al cargar el historial.</p>');
        });
    });
});
</script>
@endpush

</x-app-layout>
