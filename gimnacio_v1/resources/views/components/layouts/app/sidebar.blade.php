<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800 antialiased">
        <flux:sidebar sticky collapsible="mobile" class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
            <flux:sidebar.header>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 px-2 py-2 min-w-0" wire:navigate>
                    <img src="{{ asset('Open9/logo_completo_sin_fondo.png') }}" alt="{{ config('app.name', 'Open9') }}" class="h-8 max-h-8 w-auto object-contain" />
                </a>

                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.search placeholder="Buscar..." />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:sidebar.item>

                <flux:sidebar.group expandable heading="Gestión de Clientes" class="grid" :expanded="request()->routeIs('clientes.*') || request()->routeIs('membresias.*') || request()->routeIs('cliente-matriculas.*') || request()->routeIs('checking.*') || request()->routeIs('clases.*')">
                    <flux:sidebar.item icon="users" :href="route('clientes.index')" :current="request()->routeIs('clientes.*')" wire:navigate>
                        {{ __('Clientes') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="identification" :href="route('membresias.index')" :current="request()->routeIs('membresias.*')" wire:navigate>
                        {{ __('Membresías') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="academic-cap" :href="route('clases.index')" :current="request()->routeIs('clases.*')" wire:navigate>
                        {{ __('Clases') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="user-group" :href="route('cliente-matriculas.index')" :current="request()->routeIs('cliente-matriculas.*')" wire:navigate>
                        {{ __('Cliente Matrículas') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="check-circle" :href="route('checking.index')" :current="request()->routeIs('checking.*')" wire:navigate>
                        {{ __('Checking') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group expandable heading="Gestión Nutricional" class="grid" :expanded="request()->routeIs('gestion-nutricional.*')">
                    <flux:sidebar.item icon="clipboard-document-list" :href="route('gestion-nutricional.index')" :current="request()->routeIs('gestion-nutricional.index')" wire:navigate>
                        {{ __('Gestión Nutricional') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="calendar" :href="route('gestion-nutricional.calendario')" :current="request()->routeIs('gestion-nutricional.calendario')" wire:navigate>
                        {{ __('Calendario') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group expandable heading="CRM" class="grid" :expanded="request()->routeIs('crm.*')">
                    <flux:sidebar.item icon="chat-bubble-left-right" :href="route('crm.mensajes')" :current="request()->routeIs('crm.mensajes')" wire:navigate>
                        {{ __('Mensajes WhatsApp') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group expandable heading="Ventas" class="grid" :expanded="request()->routeIs('cajas.*') || request()->routeIs('pos.*')">
                    <flux:sidebar.item icon="banknotes" :href="route('cajas.index')" :current="request()->routeIs('cajas.*')" wire:navigate>
                        {{ __('Caja') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="shopping-cart" :href="route('pos.index')" :current="request()->routeIs('pos.*')" wire:navigate>
                        {{ __('Punto de Venta') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group expandable heading="Productos" class="grid" :expanded="request()->routeIs('categorias-productos.*') || request()->routeIs('productos.*')">
                    <flux:sidebar.item icon="tag" :href="route('categorias-productos.index')" :current="request()->routeIs('categorias-productos.*')" wire:navigate>
                        {{ __('Categorías Productos') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="cube" :href="route('productos.index')" :current="request()->routeIs('productos.*')" wire:navigate>
                        {{ __('Productos') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group expandable heading="Servicios" class="grid" :expanded="request()->routeIs('servicios.*')">
                    <flux:sidebar.item icon="wrench-screwdriver" :href="route('servicios.index')" :current="request()->routeIs('servicios.*')" wire:navigate>
                        {{ __('Servicios') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group expandable heading="Integración BioTime" class="grid" :expanded="request()->routeIs('biotime.*')">
                    <flux:sidebar.item icon="signal" :href="route('biotime.index')" :current="request()->routeIs('biotime.index')" wire:navigate>
                        {{ __('BioTime Dashboard') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="cog-6-tooth" :href="route('biotime.config')" :current="request()->routeIs('biotime.config')" wire:navigate>
                        {{ __('Configuración BioTime') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="arrow-path" :href="route('biotime.sync')" :current="request()->routeIs('biotime.sync')" wire:navigate>
                        {{ __('Sincronizar BioTime') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="map-pin" :href="route('biotime.areas')" :current="request()->routeIs('biotime.areas')" wire:navigate>
                        {{ __('Áreas BioTime') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="building-office-2" :href="route('biotime.departments')" :current="request()->routeIs('biotime.departments')" wire:navigate>
                        {{ __('Departamentos BioTime') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="users" :href="route('biotime.employees')" :current="request()->routeIs('biotime.employees')" wire:navigate>
                        {{ __('Empleados BioTime') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                @if(auth()->user()->hasAnyRole(['super_administrador', 'administrador']))
                <flux:sidebar.group expandable heading="Administración" class="grid" :expanded="request()->routeIs('usuarios.*') || request()->routeIs('roles.*')">
                    <flux:sidebar.item icon="users" :href="route('usuarios.index')" :current="request()->routeIs('usuarios.*')" wire:navigate>
                        {{ __('Usuarios') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="shield-check" :href="route('roles.index')" :current="request()->routeIs('roles.*')" wire:navigate>
                        {{ __('Roles') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
                @endif
            </flux:sidebar.nav>

            <flux:sidebar.spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="cog-6-tooth" :href="route('profile.edit')" :current="request()->routeIs('profile.*')" wire:navigate>
                    {{ __('Settings') }}
                </flux:sidebar.item>
                <flux:sidebar.item icon="information-circle" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                    {{ __('Help') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>

            <flux:dropdown position="top" align="start" class="max-lg:hidden">
                <flux:sidebar.profile 
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>
                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog-6-tooth" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full" data-test="logout-button">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <flux:header class="block! bg-white lg:bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
            <flux:navbar class="lg:hidden w-full">
                <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

                <flux:spacer />

                <flux:dropdown position="top" align="start">
                    <flux:profile 
                        :initials="auth()->user()->initials()"
                    />

                    <flux:menu>
                        <flux:menu.radio.group>
                            <div class="p-0 text-sm font-normal">
                                <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                    <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                        <span class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                            {{ auth()->user()->initials() }}
                                        </span>
                                    </span>
                                    <div class="grid flex-1 text-start text-sm leading-tight">
                                        <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                        <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                    </div>
                                </div>
                            </div>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <flux:menu.radio.group>
                            <flux:menu.item :href="route('profile.edit')" icon="cog-6-tooth" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full" data-test="logout-button">
                                {{ __('Log Out') }}
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            </flux:navbar>

            <flux:navbar scrollable>
                <flux:navbar.item :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:navbar.item>
                <flux:navbar.item :href="route('checking.index')" :current="request()->routeIs('checking.*')" wire:navigate>
                    {{ __('Checking') }}
                </flux:navbar.item>
            </flux:navbar>
        </flux:header>

        
        {{ $slot }}
        

        @fluxScripts
        @stack('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    </body>
</html>
