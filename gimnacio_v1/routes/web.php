<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('dashboard', \App\Livewire\Dashboard\DashboardLive::class)->middleware(['auth', 'verified'])->name('dashboard');

// Descarga de reporte con URL firmada (para enlace enviado por WhatsApp al cliente; sin auth, válida 48 h)
Route::get('reportes/evaluacion/descargar/{evaluacionId}', [\App\Http\Controllers\ReporteController::class, 'descargarEvaluacion'])
    ->middleware(['signed'])
    ->name('reportes.evaluacion.descargar.signed');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    // Clientes
    Route::get('clientes', \App\Livewire\Clientes\ClienteLive::class)->name('clientes.index');

    // Membresías
    Route::get('membresias', \App\Livewire\Membresias\MembresiaLive::class)->name('membresias.index');

    // Matrículas de Clientes (Membresías y Clases)
    Route::get('cliente-matriculas', \App\Livewire\ClienteMatriculas\ClienteMatriculaLive::class)->name('cliente-matriculas.index');

    // Cajas
    Route::get('cajas', \App\Livewire\Cajas\CajaLive::class)->name('cajas.index');

    // Checking - Registro de Ingreso
    Route::get('checking', \App\Livewire\Checking\CheckingLive::class)->name('checking.index');

    // Punto de Venta
    Route::get('pos', \App\Livewire\POS\POSLive::class)->name('pos.index');

    // Catálogos
    Route::get('categorias-productos', \App\Livewire\Categorias\CategoriaProductoLive::class)->name('categorias-productos.index');
    Route::get('productos', \App\Livewire\Productos\ProductoLive::class)->name('productos.index');
    Route::get('servicios', \App\Livewire\Servicios\ServicioExternoLive::class)->name('servicios.index');
    Route::get('clases', \App\Livewire\Clases\ClaseLive::class)->name('clases.index');

    // Reportes (previsualización e impresión/descarga)
    Route::prefix('reportes')->name('reportes.')->group(function () {
        Route::get('evaluacion/{evaluacionId}/preview', [\App\Http\Controllers\ReporteController::class, 'previewEvaluacion'])->name('evaluacion.preview');
        Route::get('evaluacion/{evaluacionId}/descargar', [\App\Http\Controllers\ReporteController::class, 'descargarEvaluacion'])->name('evaluacion.descargar');
        Route::get('historial-cliente/{clienteId}/preview', [\App\Http\Controllers\ReporteController::class, 'previewHistorialCliente'])->name('historial-cliente.preview');
        Route::get('historial-cliente/{clienteId}/descargar', [\App\Http\Controllers\ReporteController::class, 'descargarHistorialCliente'])->name('historial-cliente.descargar');
        Route::get('composicion-corporal/{clienteId}/preview', [\App\Http\Controllers\ReporteController::class, 'previewComposicionCorporal'])->name('composicion-corporal.preview');
        Route::get('composicion-corporal/{clienteId}/descargar', [\App\Http\Controllers\ReporteController::class, 'descargarComposicionCorporal'])->name('composicion-corporal.descargar');
    });

    // Gestión Nutricional (módulo unificado: Medidas, Nutrición, Citas, Calendario)
    Route::get('gestion-nutricional', \App\Livewire\GestionNutricional\GestionNutricionalUnificadoLive::class)->name('gestion-nutricional.index');
    Route::get('gestion-nutricional/calendario', \App\Livewire\GestionNutricional\CalendarioCitasLive::class)->name('gestion-nutricional.calendario');
    Route::get('gestion-nutricional/calendario/eventos', function (\Illuminate\Http\Request $request) {
        $start = $request->get('start', now()->startOfMonth()->toIso8601String());
        $end = $request->get('end', now()->endOfMonth()->toIso8601String());
        $service = app(\App\Services\CitaService::class);
        return response()->json($service->getEventosParaCalendario($start, $end)->values());
    })->name('gestion-nutricional.calendario.eventos');
    
    // Redirecciones para mantener compatibilidad con rutas antiguas
    Route::redirect('gestion-nutricional/medidas', 'gestion-nutricional', 301);
    Route::redirect('gestion-nutricional/nutricion', 'gestion-nutricional', 301);
    Route::redirect('gestion-nutricional/citas', 'gestion-nutricional', 301);
    Route::redirect('medidas-nutricion', 'gestion-nutricional', 301)->name('medidas-nutricion.index');

    // CRM
    Route::get('crm/mensajes', \App\Livewire\Crm\MensajesLive::class)->name('crm.mensajes');

    // Administración (super_administrador y administrador)
    Route::middleware(['role:super_administrador|administrador'])->group(function () {
        Route::get('usuarios', \App\Livewire\Usuarios\UsuarioLive::class)->name('usuarios.index');
        Route::get('roles', \App\Livewire\Roles\RolLive::class)->name('roles.index');
    });

    // Integración BioTime (ZKTeco)
    Route::get('biotime', \App\Livewire\Biotime\BiotimeIndexLive::class)->name('biotime.index');
    Route::get('biotime/config', \App\Livewire\Biotime\BiotimeConfigLive::class)->name('biotime.config');
    Route::get('biotime/sync', \App\Livewire\Biotime\BiotimeSyncLive::class)->name('biotime.sync');
    Route::get('biotime/areas', \App\Livewire\Biotime\Area\AreaIndexLive::class)->name('biotime.areas');
    Route::get('biotime/departments', \App\Livewire\Biotime\Department\DepartmentIndexLive::class)->name('biotime.departments');
    Route::get('biotime/employees', \App\Livewire\Biotime\Employees\EmployeesIndexLive::class)->name('biotime.employees');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});
