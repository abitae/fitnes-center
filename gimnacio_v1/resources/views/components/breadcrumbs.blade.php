@php
    $routeName = request()->route()?->getName();
    if (! $routeName || $routeName === 'dashboard') {
        $segments = [['label' => __('Dashboard'), 'url' => route('dashboard')]];
    } else {
        $labels = [
            'clientes.index' => __('Clientes'),
            'membresias.index' => __('Membresías'),
            'cliente-matriculas.index' => __('Cliente Matrículas'),
            'clases.index' => __('Clases'),
            'checking.index' => __('Checking'),
            'cajas.index' => __('Caja'),
            'pos.index' => __('Punto de Venta'),
            'categorias-productos.index' => __('Categorías Productos'),
            'productos.index' => __('Productos'),
            'servicios.index' => __('Servicios'),
            'gestion-nutricional.index' => __('Gestión Nutricional'),
            'gestion-nutricional.calendario' => __('Calendario'),
            'crm.mensajes' => __('Mensajes WhatsApp'),
            'usuarios.index' => __('Usuarios'),
            'roles.index' => __('Roles'),
            'biotime.index' => __('BioTime Dashboard'),
            'biotime.config' => __('Configuración BioTime'),
            'biotime.sync' => __('Sincronizar BioTime'),
            'biotime.areas' => __('Áreas BioTime'),
            'biotime.departments' => __('Departamentos BioTime'),
            'biotime.employees' => __('Empleados BioTime'),
            'profile.edit' => __('Settings'),
            'user-password.edit' => __('Contraseña'),
            'appearance.edit' => __('Apariencia'),
            'two-factor.show' => __('Autenticación en dos pasos'),
        ];
        $segments = [['label' => __('Dashboard'), 'url' => route('dashboard')]];
        if (isset($labels[$routeName])) {
            $urls = [
                'gestion-nutricional.calendario' => route('gestion-nutricional.calendario'),
                'biotime.config' => route('biotime.config'),
                'biotime.sync' => route('biotime.sync'),
                'biotime.areas' => route('biotime.areas'),
                'biotime.departments' => route('biotime.departments'),
                'biotime.employees' => route('biotime.employees'),
            ];
            $parentLabel = null;
            if (str_starts_with($routeName, 'gestion-nutricional.') && $routeName !== 'gestion-nutricional.index') {
                $parentLabel = __('Gestión Nutricional');
            }
            if (str_starts_with($routeName, 'biotime.') && $routeName !== 'biotime.index') {
                $parentLabel = __('Integración BioTime');
            }
            if ($parentLabel) {
                $parentRoute = $routeName === 'gestion-nutricional.calendario' ? 'gestion-nutricional.index' : 'biotime.index';
                $segments[] = ['label' => $parentLabel, 'url' => route($parentRoute)];
            }
            $segments[] = ['label' => $labels[$routeName], 'url' => $urls[$routeName] ?? null];
        }
    }
@endphp
@if (count($segments) > 0)
<nav aria-label="{{ __('Miga de pan') }}" class="mb-3 flex items-center gap-1.5 text-xs text-zinc-500 dark:text-zinc-400">
    @foreach ($segments as $i => $seg)
        @if ($i > 0)
            <span aria-hidden="true">/</span>
        @endif
        @if ($i === count($segments) - 1)
            <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $seg['label'] }}</span>
        @else
            <a href="{{ $seg['url'] }}" wire:navigate class="hover:text-zinc-900 dark:hover:text-zinc-200">{{ $seg['label'] }}</a>
        @endif
    @endforeach
</nav>
@endif
