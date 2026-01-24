<div class="d-flex flex-column h-100">
    {{-- Logo --}}
    <div class="d-flex justify-content-center align-items-center py-3 border-bottom">
        <a href="/" class="text-decoration-none">
            <img src="{{ asset('images/logodorado2.png') }}" class="logo-full" width="100" alt="Logo">
            <img src="{{ asset('images/logoico.png') }}" class="logo-icon d-none" width="40" alt="Logo Icon">
        </a>
    </div>

    {{-- Navegación --}}
    <nav class="nav flex-column px-2 py-3">
<a href="/dashboard"
   class="nav-link mb-2 d-flex align-items-center gap-2 {{ request()->is('dashboard') ? 'active' : 'text-dark' }}">
    <i class="bi bi-speedometer2"></i>
    <span>Inicio</span>
</a>
@if (auth()->user()->getRoleNames()->first() == 'admin')
<a href="/usuarios"
   class="nav-link mb-2 d-flex align-items-center gap-2 {{ request()->is('usuarios*') ? 'active' : 'text-dark' }}">
    <i class="bi bi-person-lines-fill"></i>
    <span>Usuarios</span>
</a>
@endif
@if (auth()->user()->getRoleNames()->first() == 'admin' || auth()->user()->getRoleNames()->first() == 'traige')
    <a href="/traige"
    class="nav-link mb-2 d-flex align-items-center gap-2 {{ request()->is('traige*') ? 'active' : 'text-dark' }}">
        <i class="bi bi-funnel-fill"></i>
        <span>Triage</span>
    </a>
@endif
@if (auth()->user()->getRoleNames()->first() == 'admin' || auth()->user()->getRoleNames()->first() == 'closer')
    <a href="/leads"
    class="nav-link mb-2 d-flex align-items-center gap-2 {{ request()->is('leads*') && !request()->is('onboarding/leads*') ? 'active' : 'text-dark' }}">
        <i class="bi bi-person-plus-fill"></i>
        <span>Closer</span>
    </a>

    <a href="/llamadas"
    class="nav-link mb-2 d-flex align-items-center gap-2 {{ request()->is('llamadas*') ? 'active' : 'text-dark' }}">
        <i class="bi bi-telephone-fill"></i>
        <span>Llamadas</span>
    </a>
@endif

@if (auth()->user()->getRoleNames()->first() == 'admin' || auth()->user()->getRoleNames()->first() == 'cms')
    <!-- Operaciones Dropdown -->
    <div class="nav-item dropdown mb-2">
        <a class="nav-link d-flex align-items-center gap-2 dropdown-toggle {{ request()->is('onboarding/dashboard*') ? 'active' : 'text-dark' }}"
           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-bar-chart-line"></i>
            <span>Operaciones</span>
        </a>
        <ul class="dropdown-menu">
            <li>
                <a class="dropdown-item d-flex align-items-center gap-2 {{ request()->is('onboarding/dashboard*') ? 'active' : '' }}"
                   href="/onboarding/dashboard">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span>Dashboard</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Fulfillment Dropdown -->
    <div class="nav-item dropdown mb-2">
        <a class="nav-link d-flex align-items-center gap-2 dropdown-toggle {{ request()->is('onboarding*') && !request()->is('onboarding/dashboard*') || request()->is('upsell*') || request()->is('fulfillment*') ? 'active' : 'text-dark' }}"
           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-headset"></i>
            <span>Fulfillment</span>
        </a>
        <ul class="dropdown-menu">
            <li>
                <a class="dropdown-item d-flex align-items-center gap-2 {{ request()->is('onboarding/leads*') ? 'active' : '' }}"
                   href="/onboarding/leads">
                    <i class="bi bi-people"></i>
                    <span>Gestión de Leads</span>
                </a>
            </li>
            <li>
                <a class="dropdown-item d-flex align-items-center gap-2 {{ request()->is('contracts/approval*') ? 'active' : '' }}"
                   href="/contracts/approval">
                    <i class="bi bi-file-earmark-check"></i>
                    <span>Aprobación de Contratos</span>
                </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item d-flex align-items-center gap-2 {{ request()->is('upsell*') ? 'active' : '' }}"
                   href="/upsell">
                    <i class="bi bi-arrow-up-circle"></i>
                    <span>Upsells</span>
                </a>
            </li>
            <li>
                <a class="dropdown-item d-flex align-items-center gap-2 {{ request()->is('fulfillment/form*') ? 'active' : '' }}"
                   href="/fulfillment/form">
                    <i class="bi bi-clipboard-check"></i>
                    <span>FULFILLMENT DAILY</span>
                </a>
            </li>
        </ul>
    </div>
@endif

@if (auth()->user()->getRoleNames()->first() == 'admin' || auth()->user()->getRoleNames()->first() == 'cms')
    <a href="/forms"
       class="nav-link mb-2 d-flex align-items-center gap-2 {{ request()->is('forms*') ? 'active' : 'text-dark' }}">
        <i class="bi bi-ui-checks-grid"></i>
        <span>Formularios</span>
    </a>
@endif

@if (auth()->user()->getRoleNames()->first() == 'admin' || auth()->user()->getRoleNames()->first() == 'finanzas')
    <!-- Finanzas Dropdown -->
    <div class="nav-item dropdown mb-2">
        <a class="nav-link d-flex align-items-center gap-2 dropdown-toggle {{ request()->is('finanzas*') ? 'active' : 'text-dark' }}"
           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-cash-coin"></i>
            <span>Finanzas</span>
        </a>
        <ul class="dropdown-menu">
            <li>
                <a class="dropdown-item d-flex align-items-center gap-2 {{ request()->is('finanzas/dashboard*') ? 'active' : '' }}"
                   href="/finanzas/dashboard">
                    <i class="bi bi-graph-up"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a class="dropdown-item d-flex align-items-center gap-2 {{ request()->is('finanzas/cuotas*') ? 'active' : '' }}"
                   href="/finanzas/cuotas">
                    <i class="bi bi-calendar-check"></i>
                    <span>Cuotas</span>
                </a>
            </li>
        </ul>
    </div>
@endif

@if (auth()->user()->getRoleNames()->first() == 'admin')
    <!-- Configuracion Dropdown -->
    <div class="nav-item dropdown mb-2">
        <a class="nav-link d-flex align-items-center gap-2 dropdown-toggle {{ request()->is('configuration*') ? 'active' : 'text-dark' }}"
           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-gear-fill"></i>
            <span>Configuracion</span>
        </a>
        <ul class="dropdown-menu">
            <li>
                <a class="dropdown-item d-flex align-items-center gap-2 {{ request()->is('configuration/prices*') ? 'active' : '' }}"
                   href="/configuration/prices">
                    <i class="bi bi-currency-dollar"></i>
                    <span>Precios</span>
                </a>
            </li>
        </ul>
    </div>
@endif

        {{-- Agrega más enlaces aquí si lo deseas --}}
    </nav>

    {{-- Botón Salir --}}
    <div class="mt-auto p-3 border-top">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-start gap-2">
                <i class="fas fa-arrow-right-from-bracket"></i>
                <span class="logout-label">Salir</span>
            </button>
        </form>
    </div>
</div>