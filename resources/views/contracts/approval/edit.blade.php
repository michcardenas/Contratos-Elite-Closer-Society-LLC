<x-app-layout>
    <x-slot name="header">
        {{ __('Revisar y Aprobar Contrato') }}
    </x-slot>

    <div class="py-12" style="padding-top: 0;">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <!-- Panel Izquierdo: Formulario de Edición -->
                <div class="col-lg-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="text-xl font-semibold">Editar Contrato</h4>
                                <a href="{{ route('contracts.approval.index') }}" class="btn btn-outline-secondary">
                                    ← Volver
                                </a>
                            </div>

                            <!-- Información del Cliente -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Información del Cliente</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Nombre:</strong> {{ $sale->nombre_cliente }} {{ $sale->apellido_cliente }}
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Email:</strong> {{ $sale->email_cliente }}
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Teléfono:</strong> {{ $sale->telefono_cliente }}
                                        </div>
                                        <div class="col-md-6">
                                            <strong>ID Personal:</strong> {{ $sale->identificacion_personal }}
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Fuente:</strong> {{ $sale->fuente ?? 'N/A' }}
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Fecha de Cierre:</strong> {{ $sale->fecha_cierre ? $sale->fecha_cierre->format('d/m/Y') : 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Formulario de Edición -->
                            <form id="contractEditForm" method="POST" action="{{ route('contracts.approval.update', $sale->id) }}">
                                @csrf
                                @method('PUT')

                                @foreach($sale->contractTemplate->dynamic_fields as $field)
                                    <div class="mb-3">
                                        <label class="form-label">{{ ucfirst(str_replace('_', ' ', $field)) }} <span class="text-danger">*</span></label>
                                        @if($field === 'imagen_firma')
                                            @if(isset($sale->contract_data[$field]) && $sale->contract_data[$field])
                                                <div class="mb-2">
                                                    <img src="{{ $sale->contract_data[$field] }}" alt="Firma actual" class="img-thumbnail" style="max-width: 200px;">
                                                    <input type="hidden" name="{{ $field }}" value="{{ $sale->contract_data[$field] }}">
                                                </div>
                                            @endif
                                            <input type="file" name="{{ $field }}_file" class="form-control"
                                                   accept="image/*" onchange="handleSignatureUpload(this)">
                                            <small class="text-muted">Subir nueva imagen de firma (opcional si ya existe una)</small>
                                        @elseif(strlen($field) > 15 || in_array($field, ['comentarios', 'observaciones', 'descripcion', 'forma_de_pago']))
                                            <textarea name="{{ $field }}" class="form-control" rows="3" required oninput="updatePreview()">{{ old($field, $sale->contract_data[$field] ?? '') }}</textarea>
                                        @else
                                            <input type="text" name="{{ $field }}" class="form-control"
                                                   value="{{ old($field, $sale->contract_data[$field] ?? '') }}"
                                                   required oninput="updatePreview()">
                                        @endif
                                        @error($field)
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endforeach

                                <!-- Campo para fecha de firma -->
                                <div class="mb-3">
                                    <label class="form-label">Fecha de Firma del Contrato</label>
                                    <input type="datetime-local" name="contract_signed_date" class="form-control"
                                           value="{{ old('contract_signed_date', $sale->contract_signed_date ? $sale->contract_signed_date->format('Y-m-d\TH:i') : '') }}">
                                    <small class="text-muted">Se llena automáticamente cuando el lead firma el contrato, pero puede editarse manualmente.</small>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <button type="submit" class="btn btn-primary">Actualizar Contrato</button>
                                </div>
                            </form>

                            <!-- Formulario separado para aprobación -->
                            <form method="POST" action="{{ route('contracts.approval.approve', $sale->id) }}" class="mt-3">
                                @csrf
                                <button type="submit" class="btn btn-success w-100"
                                        onclick="return confirm('¿Está seguro de que desea aprobar este contrato?')">
                                    Aprobar Contrato
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Panel Derecho: Vista Previa -->
                <div class="col-lg-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <h4 class="text-xl font-semibold mb-4">Vista Previa del Contrato</h4>

                            <div class="border" style="height: 600px; overflow-y: auto; padding: 20px; background-color: #f9f9f9;">
                                <div id="contractPreview" style="background-color: white; padding: 20px; transform: scale(0.8); transform-origin: top left; width: 125%;">
                                    <!-- La vista previa se cargará aquí -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


@push('scripts')
<script>
    function updatePreview() {
        const formData = new FormData(document.getElementById('contractEditForm'));
        const params = new URLSearchParams();

        for (const [key, value] of formData) {
            if (key !== '_method') { // Excluir el campo _method
                params.append(key, value);
            }
        }

        fetch('{{ route("contracts.approval.preview", $sale->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: params
        })
        .then(response => response.text())
        .then(html => {
            document.getElementById('contractPreview').innerHTML = html;
        })
        .catch(error => {
            console.error('Error updating preview:', error);
        });
    }

    // Manejar carga de archivo de firma
    function handleSignatureUpload(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                const signatureHidden = document.querySelector('input[name="imagen_firma"]');
                if (signatureHidden) {
                    signatureHidden.value = e.target.result;
                }
                updatePreview();
            };

            reader.readAsDataURL(file);
        }
    }

    // Cargar vista previa inicial
    document.addEventListener('DOMContentLoaded', function() {
        updatePreview();
    });
</script>
@endpush
</x-app-layout>