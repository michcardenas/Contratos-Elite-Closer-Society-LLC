<x-app-layout>
    <x-slot name="header">
        {{ __('Dashboard de Finanzas') }}
    </x-slot>

    <div class="py-12" style="padding-top: 0;">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- KPIs -->
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card bg-primary text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-1">Ventas del Mes</h6>
                                    <h3 class="mb-0">${{ number_format($stats['total_ventas_mes'], 2) }}</h3>
                                </div>
                                <i class="bi bi-cart-check fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card bg-success text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-1">Recaudado del Mes</h6>
                                    <h3 class="mb-0">${{ number_format($stats['total_recaudado_mes'], 2) }}</h3>
                                </div>
                                <i class="bi bi-cash-stack fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card bg-warning text-dark h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-1">Pendiente de Cobrar</h6>
                                    <h3 class="mb-0">${{ number_format($stats['total_pendiente'], 2) }}</h3>
                                </div>
                                <i class="bi bi-hourglass-split fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card bg-danger text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-1">Total Vencido</h6>
                                    <h3 class="mb-0">${{ number_format($stats['total_vencido'], 2) }}</h3>
                                </div>
                                <i class="bi bi-exclamation-triangle fs-1 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alertas -->
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6">
                    <div class="card border-danger h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3">
                                <i class="bi bi-calendar-x text-danger fs-4"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">{{ $stats['cuotas_vencidas'] }}</h5>
                                <small class="text-muted">Cuotas vencidas</small>
                            </div>
                            <a href="{{ route('finanzas.cuotas') }}?estado=vencida" class="btn btn-outline-danger btn-sm ms-auto">
                                Ver todas
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="card border-warning h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                                <i class="bi bi-calendar-event text-warning fs-4"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">{{ $stats['proximos_vencimientos'] }}</h5>
                                <small class="text-muted">Vencen en los proximos 7 dias</small>
                            </div>
                            <a href="{{ route('finanzas.cuotas') }}?estado=pendiente" class="btn btn-outline-warning btn-sm ms-auto">
                                Ver todas
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Graficos -->
            <div class="row g-3">
                <div class="col-12 col-lg-8">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">Recaudacion Mensual</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="recaudacionChart" height="100"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">Estado de Cuotas</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="estadoCuotasChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones Rapidas -->
            <div class="row g-3 mt-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Acciones Rapidas</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="{{ route('finanzas.cuotas') }}" class="btn btn-primary">
                                    <i class="bi bi-list-ul me-1"></i> Ver todas las cuotas
                                </a>
                                <a href="{{ route('finanzas.cuotas') }}?estado=pendiente" class="btn btn-warning">
                                    <i class="bi bi-hourglass me-1"></i> Cuotas pendientes
                                </a>
                                <a href="{{ route('finanzas.cuotas') }}?estado=vencida" class="btn btn-danger">
                                    <i class="bi bi-exclamation-circle me-1"></i> Cuotas vencidas
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Grafico de recaudacion mensual
    const recaudacionData = @json($stats['recaudacion_mensual']);
    const recaudacionCtx = document.getElementById('recaudacionChart').getContext('2d');
    new Chart(recaudacionCtx, {
        type: 'bar',
        data: {
            labels: recaudacionData.map(item => item.mes),
            datasets: [{
                label: 'Recaudacion ($)',
                data: recaudacionData.map(item => item.total),
                backgroundColor: 'rgba(40, 167, 69, 0.7)',
                borderColor: 'rgba(40, 167, 69, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '$' + context.raw.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Grafico de estado de cuotas
    const estadoCuotas = @json($stats['cuotas_por_estado']);
    const estadoCtx = document.getElementById('estadoCuotasChart').getContext('2d');
    new Chart(estadoCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pendiente', 'Pagada', 'Vencida', 'Pago Parcial'],
            datasets: [{
                data: [
                    estadoCuotas['pendiente'] || 0,
                    estadoCuotas['pagada'] || 0,
                    estadoCuotas['vencida'] || 0,
                    estadoCuotas['parcial'] || 0
                ],
                backgroundColor: [
                    '#ffc107',
                    '#28a745',
                    '#dc3545',
                    '#17a2b8'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>
@endpush

</x-app-layout>
