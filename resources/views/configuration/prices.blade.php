<x-app-layout>
    <x-slot name="header">
        {{ __('Configuracion de Precios') }}
    </x-slot>

    <div class="py-12" style="padding-top: 0;">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h4 class="text-2xl font-semibold mb-4">Configuracion de Precios por Tipo de Contrato</h4>

                    <div class="alert alert-info mb-4">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Importante:</strong> Los cambios en los precios solo afectan a las nuevas ventas. Las ventas existentes mantienen el monto con el que fueron creadas.
                    </div>

                    <div class="border border-gray-300 rounded-lg">
                        <div class="overflow-x-auto">
                            <table id="prices-table" class="table-responsive w-full text-sm text-left text-gray-700">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                    <tr class="border-b border-gray-300">
                                        <th class="px-6 py-3">Tipo de Contrato</th>
                                        <th class="px-6 py-3">Monto Base</th>
                                        <th class="px-6 py-3">Monto</th>
                                        <th class="px-6 py-3">Cuotas</th>
                                        <th class="px-6 py-3" data-priority="1">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5 class="font-semibold mb-2">Leyenda:</h5>
                        <ul class="list-disc ml-5 text-sm text-gray-600">
                            <li><span class="badge bg-secondary">Fijo</span> - El monto se toma automaticamente de esta configuracion</li>
                            <li><span class="badge bg-success">Editable</span> - El closer puede modificar el monto al crear la venta</li>
                            <li><span class="badge bg-secondary">Auto-calculadas</span> - Las cuotas se calculan automaticamente dividiendo el monto total</li>
                            <li><span class="badge bg-success">Editables</span> - El closer puede definir el monto de cada cuota manualmente</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Editar Precio -->
    <div class="modal fade" id="editPriceModal" tabindex="-1" aria-labelledby="editPriceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPriceModalLabel">Editar Configuracion de Precio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editPriceForm">
                    <div class="modal-body">
                        <input type="hidden" id="config_id" name="config_id">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Tipo de Contrato</label>
                            <p id="tipo_contrato_display" class="form-control-plaintext"></p>
                        </div>

                        <div class="mb-3">
                            <label for="monto_base" class="form-label fw-bold">Monto Base *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="monto_base" name="monto_base"
                                       step="0.01" min="0" required>
                            </div>
                            <small class="text-muted">Este es el monto que se usara por defecto para nuevas ventas</small>
                        </div>

                        <div class="mb-3">
                            <label for="moneda" class="form-label fw-bold">Moneda *</label>
                            <select class="form-select" id="moneda" name="moneda" required>
                                <option value="USD">USD - Dolar Estadounidense</option>
                                <option value="ARS">ARS - Peso Argentino</option>
                            </select>
                        </div>

                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Nota:</strong> Las opciones de edicion de monto y cuotas son fijas por tipo de contrato y no pueden modificarse.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tipoContratoLabels = {
        'low_ticket': 'Low Ticket',
        'high_ticket': 'High Ticket',
        'beca': 'Beca',
        'formacion_anexo': 'Acuerdo de Formacion y Anexo'
    };

    const table = $('#prices-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        scrollX: true,
        autoWidth: false,
        ajax: "{{ route('configuration.prices.index') }}",
        columns: [
            { data: 'tipo_contrato_label', name: 'tipo_contrato' },
            { data: 'monto_formateado', name: 'monto_base' },
            { data: 'monto_editable_badge', name: 'monto_editable', orderable: false },
            { data: 'cuotas_editables_badge', name: 'cuotas_editables', orderable: false },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false,
                className: 'noVis'
            }
        ],
        dom: "<'flex flex-wrap justify-between items-center mb-4'<'relative'B>f>" +
             "t" +
             "<'flex justify-between items-center px-2 my-2'i<'pagination-wrapper'p>>",
        buttons: [
            {
                extend: 'pageLength',
                className: 'btn btn-outline-dark',
                text: 'Filas '
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

    // Abrir modal de edicion
    $(document).on('click', '.btn-edit', function() {
        const configId = $(this).data('id');

        $.get(`{{ url('configuration/prices') }}/${configId}`, function(data) {
            $('#config_id').val(data.id);
            $('#tipo_contrato_display').text(tipoContratoLabels[data.tipo_contrato] || data.tipo_contrato);
            $('#monto_base').val(parseFloat(data.monto_base).toFixed(2));
            $('#moneda').val(data.moneda);

            $('#editPriceModal').modal('show');
        }).fail(function() {
            Swal.fire('Error', 'No se pudo cargar la configuracion', 'error');
        });
    });

    // Guardar cambios
    $('#editPriceForm').on('submit', function(e) {
        e.preventDefault();

        const configId = $('#config_id').val();
        const formData = {
            monto_base: $('#monto_base').val(),
            moneda: $('#moneda').val(),
            _token: '{{ csrf_token() }}'
        };

        $.ajax({
            url: `{{ url('configuration/prices') }}/${configId}`,
            method: 'PUT',
            data: formData,
            success: function(response) {
                $('#editPriceModal').modal('hide');
                table.ajax.reload();
                Swal.fire({
                    icon: 'success',
                    title: 'Guardado',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors;
                let errorMessage = 'Error al guardar los cambios';

                if (errors) {
                    errorMessage = Object.values(errors).flat().join('\n');
                }

                Swal.fire('Error', errorMessage, 'error');
            }
        });
    });
});
</script>
@endpush

</x-app-layout>
