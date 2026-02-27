<?php

namespace App\Livewire\GestionNutricional;

use App\Models\Core\EvaluacionMedidasNutricion;
use App\Services\CitaService;
use App\Services\ClienteService;
use App\Services\EvaluacionMedidasNutricionService;
use App\Services\ReporteService;
use App\Services\SeguimientoNutricionService;
use Livewire\Component;
use Livewire\WithPagination;

class GestionNutricionalUnificadoLive extends Component
{
    use WithPagination;

    // Cliente search
    public $clienteSearch = '';
    public $clientes;
    public $selectedClienteId = null;
    public $selectedCliente = null;
    public $isSearching = false;

    // Tab principal (ficha_salud, historial, nutricion, citas)
    public $mainTab = 'ficha_salud';

    // Filtros
    public $estadoFilter = '';
    public $tipoFilter = '';
    public $perPage = 15;

    // Modal states
    public $modalState = [
        'evaluacion' => false,
        'delete_evaluacion' => false,
        'nutricion' => false,
        'delete_nutricion' => false,
        'cita' => false,
        'delete_cita' => false,
        'reporte_preview' => false,
    ];

    /** ID de evaluación para el reporte en previsualización */
    public $evaluacionIdReporte = null;

    // IDs
    public $evaluacionId = null;
    public $seguimientoId = null;
    public $citaId = null;

    // Form data - Evaluación
    public $evaluacionFormData = [
        'peso' => '',
        'estatura' => '',
        'imc' => '',
        'porcentaje_grasa' => '',
        'porcentaje_musculo' => '',
        'masa_muscular' => '',
        'masa_grasa' => '',
        'masa_osea' => '',
        'masa_residual' => '',
        'circunferencias' => [
            'estatura' => '', 'cuello' => '', 'brazo_normal' => '', 'brazo_contraido' => '',
            'torax' => '', 'cintura' => '', 'cintura_baja' => '', 'cadera' => '',
            'muslo' => '', 'gluteos' => '', 'pantorrilla' => '',
        ],
        'presion_arterial' => '',
        'frecuencia_cardiaca' => '',
        'objetivo' => 'DEPORTES Ó SALUD',
        'nutricionista_id' => '',
        'fecha_proxima_evaluacion' => '',
        'estado' => 'completada',
        'observaciones' => '',
    ];

    // Form data - Nutrición
    public $nutricionFormData = [
        'tipo' => 'seguimiento',
        'fecha' => '',
        'objetivo' => '',
        'calorias_objetivo' => '',
        'contenido' => '',
        'estado' => 'activo',
        'nutricionista_id' => '',
        'cita_id' => '',
    ];

    // Form data - Cita
    public $citaFormData = [
        'tipo' => 'evaluacion',
        'fecha_hora' => '',
        'duracion_minutos' => 60,
        'nutricionista_id' => '',
        'trainer_user_id' => '',
        'estado' => 'programada',
        'observaciones' => '',
    ];

    protected $paginationTheme = 'tailwind';

    protected EvaluacionMedidasNutricionService $evaluacionService;
    protected SeguimientoNutricionService $seguimientoService;
    protected CitaService $citaService;
    protected ClienteService $clienteService;
    protected ReporteService $reporteService;

    public function boot(
        EvaluacionMedidasNutricionService $evaluacionService,
        SeguimientoNutricionService $seguimientoService,
        CitaService $citaService,
        ClienteService $clienteService,
        ReporteService $reporteService
    ) {
        $this->evaluacionService = $evaluacionService;
        $this->seguimientoService = $seguimientoService;
        $this->citaService = $citaService;
        $this->clienteService = $clienteService;
        $this->reporteService = $reporteService;
    }

    public function mount()
    {
        $this->clientes = collect([]);
        $this->evaluacionFormData['evaluado_por'] = auth()->id();
        $this->nutricionFormData['fecha'] = now()->format('Y-m-d');
        $this->citaFormData['fecha_hora'] = now()->addDay()->format('Y-m-d\TH:i');
    }

    public function updatingClienteSearch($value)
    {
        $this->isSearching = true;
        if ($this->selectedCliente) {
            $nombreCompleto = $this->selectedCliente->nombres . ' ' . $this->selectedCliente->apellidos;
            $valorTrim = trim($value);
            if ($valorTrim !== $nombreCompleto && $valorTrim !== '') {
                $this->selectedClienteId = null;
                $this->selectedCliente = null;
            }
        }
    }

    public function updatedClienteSearch()
    {
        $searchTerm = trim($this->clienteSearch);
        if (strlen($searchTerm) >= 2) {
            $this->clientes = $this->clienteService->quickSearch($searchTerm, 10);
        } else {
            $this->clientes = collect([]);
        }
        $this->isSearching = false;
    }

    public function selectCliente($clienteId)
    {
        $this->selectedClienteId = $clienteId;
        $this->selectedCliente = $this->clienteService->find($clienteId);
        if ($this->selectedCliente) {
            $this->clienteSearch = $this->selectedCliente->nombres . ' ' . $this->selectedCliente->apellidos;
        }
        $this->clientes = collect([]);
        $this->resetPage();
    }

    public function clearClienteSelection()
    {
        $this->selectedClienteId = null;
        $this->selectedCliente = null;
        $this->clienteSearch = '';
        $this->clientes = collect([]);
        $this->isSearching = false;
        $this->resetPage();
    }

    // ========== EVALUACIONES (MEDIDAS) ==========
    public function openCreateEvaluacionModal()
    {
        if (! $this->selectedClienteId) {
            session()->flash('error', 'Debes seleccionar un cliente primero');
            return;
        }
        $this->resetEvaluacionForm();
        $this->evaluacionFormData['evaluado_por'] = auth()->id();
        $this->modalState['evaluacion'] = true;
    }

    public function openEditEvaluacionModal($id)
    {
        $evaluacion = $this->evaluacionService->find($id);
        if (! $evaluacion) {
            session()->flash('error', 'Evaluación no encontrada');
            return;
        }
        $this->evaluacionId = $evaluacion->id;
        $this->mapEvaluacionToForm($evaluacion);
        $this->evaluacionFormData['evaluado_por'] = auth()->id();
        $this->modalState['evaluacion'] = true;
    }

    public function openDeleteEvaluacionModal($id)
    {
        $this->evaluacionId = $id;
        $this->modalState['delete_evaluacion'] = true;
    }

    public function saveEvaluacion()
    {
        try {
            if (! $this->selectedClienteId) {
                session()->flash('error', 'Debes seleccionar un cliente primero');
                return;
            }
            $data = $this->mapEvaluacionFormToData();
            $data['cliente_id'] = $this->selectedClienteId;
            $data['evaluado_por'] = auth()->id();

            if ($this->evaluacionId) {
                $this->evaluacionService->update($this->evaluacionId, $data);
                session()->flash('success', 'Evaluación actualizada correctamente');
            } else {
                $this->evaluacionService->create($data);
                session()->flash('success', 'Evaluación creada correctamente');
            }
            $this->closeEvaluacionModal();
            $this->resetPage();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->handleValidationErrors($e);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function deleteEvaluacion()
    {
        try {
            $this->evaluacionService->delete($this->evaluacionId);
            session()->flash('success', 'Evaluación eliminada correctamente');
            $this->closeEvaluacionModal();
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function closeEvaluacionModal()
    {
        $this->modalState['evaluacion'] = false;
        $this->modalState['delete_evaluacion'] = false;
        $this->evaluacionId = null;
        $this->resetEvaluacionForm();
    }

    public function updatedEvaluacionFormDataPeso()
    {
        $this->calcularIMC();
    }

    public function updatedEvaluacionFormDataEstatura()
    {
        $this->calcularIMC();
    }

    protected function calcularIMC()
    {
        if ($this->evaluacionFormData['peso'] && $this->evaluacionFormData['estatura'] && $this->evaluacionFormData['estatura'] > 0) {
            $this->evaluacionFormData['imc'] = round(
                $this->evaluacionFormData['peso'] / ($this->evaluacionFormData['estatura'] * $this->evaluacionFormData['estatura']),
                2
            );
        }
    }

    /**
     * Abre el modal de previsualización del reporte de evaluación.
     * El usuario puede imprimir o descargar desde el modal.
     */
    public function abrirPreviewReporte($evaluacionId)
    {
        $this->evaluacionIdReporte = $evaluacionId;
        $this->modalState['reporte_preview'] = true;
    }

    public function cerrarPreviewReporte()
    {
        $this->modalState['reporte_preview'] = false;
        $this->evaluacionIdReporte = null;
    }

    // ========== NUTRICIÓN ==========
    public function openCreateNutricionModal()
    {
        if (! $this->selectedClienteId) {
            session()->flash('error', 'Selecciona un cliente primero');
            return;
        }
        $this->seguimientoId = null;
        $this->nutricionFormData = [
            'tipo' => 'seguimiento',
            'fecha' => now()->format('Y-m-d'),
            'objetivo' => '',
            'calorias_objetivo' => '',
            'contenido' => '',
            'estado' => 'activo',
            'nutricionista_id' => '',
            'cita_id' => '',
        ];
        $this->modalState['nutricion'] = true;
    }

    public function openEditNutricionModal($id)
    {
        $seg = $this->seguimientoService->find($id);
        if (! $seg) {
            session()->flash('error', 'Seguimiento no encontrado');
            return;
        }
        $this->seguimientoId = $seg->id;
        $this->nutricionFormData = [
            'tipo' => $seg->tipo,
            'fecha' => $seg->fecha->format('Y-m-d'),
            'objetivo' => $seg->objetivo ?? '',
            'calorias_objetivo' => $seg->calorias_objetivo ?? '',
            'contenido' => $seg->contenido ?? '',
            'estado' => $seg->estado,
            'nutricionista_id' => $seg->nutricionista_id ?? '',
            'cita_id' => $seg->cita_id ?? '',
        ];
        $this->modalState['nutricion'] = true;
    }

    public function openDeleteNutricionModal($id)
    {
        $this->seguimientoId = $id;
        $this->modalState['delete_nutricion'] = true;
    }

    public function saveNutricion()
    {
        try {
            if (! $this->selectedClienteId) {
                session()->flash('error', 'Selecciona un cliente');
                return;
            }
            $data = [
                'cliente_id' => $this->selectedClienteId,
                'tipo' => $this->nutricionFormData['tipo'],
                'fecha' => $this->nutricionFormData['fecha'],
                'objetivo' => $this->nutricionFormData['objetivo'] ?: null,
                'calorias_objetivo' => $this->nutricionFormData['calorias_objetivo'] ? (int) $this->nutricionFormData['calorias_objetivo'] : null,
                'contenido' => $this->nutricionFormData['contenido'] ?: null,
                'estado' => $this->nutricionFormData['estado'],
                'nutricionista_id' => $this->nutricionFormData['nutricionista_id'] ?: null,
                'cita_id' => $this->nutricionFormData['cita_id'] ?: null,
            ];
            if ($this->seguimientoId) {
                $this->seguimientoService->update($this->seguimientoId, $data);
                session()->flash('success', 'Seguimiento actualizado');
            } else {
                $this->seguimientoService->create($data);
                session()->flash('success', 'Seguimiento creado');
            }
            $this->modalState['nutricion'] = false;
            $this->seguimientoId = null;
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function deleteNutricion()
    {
        try {
            $this->seguimientoService->delete($this->seguimientoId);
            session()->flash('success', 'Seguimiento eliminado');
            $this->modalState['delete_nutricion'] = false;
            $this->seguimientoId = null;
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    // ========== CITAS ==========
    public function openCreateCitaModal()
    {
        if (! $this->selectedClienteId) {
            session()->flash('error', 'Selecciona un cliente primero');
            return;
        }
        $this->citaId = null;
        $this->citaFormData = [
            'tipo' => 'evaluacion',
            'fecha_hora' => now()->addDay()->format('Y-m-d\TH:i'),
            'duracion_minutos' => 60,
            'nutricionista_id' => '',
            'trainer_user_id' => '',
            'estado' => 'programada',
            'observaciones' => '',
        ];
        $this->modalState['cita'] = true;
    }

    public function openEditCitaModal($id)
    {
        $cita = $this->citaService->find($id);
        if (! $cita) {
            session()->flash('error', 'Cita no encontrada');
            return;
        }
        $this->citaId = $cita->id;
        $this->citaFormData = [
            'tipo' => $cita->tipo,
            'fecha_hora' => $cita->fecha_hora->format('Y-m-d\TH:i'),
            'duracion_minutos' => $cita->duracion_minutos ?? 60,
            'nutricionista_id' => $cita->nutricionista_id ?? '',
            'trainer_user_id' => $cita->trainer_user_id ?? '',
            'estado' => $cita->estado,
            'observaciones' => $cita->observaciones ?? '',
        ];
        $this->modalState['cita'] = true;
    }

    public function openDeleteCitaModal($id)
    {
        $this->citaId = $id;
        $this->modalState['delete_cita'] = true;
    }

    public function saveCita()
    {
        try {
            if (! $this->selectedClienteId) {
                session()->flash('error', 'Selecciona un cliente');
                return;
            }
            $data = [
                'cliente_id' => $this->selectedClienteId,
                'tipo' => $this->citaFormData['tipo'],
                'fecha_hora' => $this->citaFormData['fecha_hora'],
                'duracion_minutos' => (int) ($this->citaFormData['duracion_minutos'] ?? 60),
                'nutricionista_id' => $this->citaFormData['nutricionista_id'] ?: null,
                'trainer_user_id' => $this->citaFormData['trainer_user_id'] ?: null,
                'estado' => $this->citaFormData['estado'],
                'observaciones' => $this->citaFormData['observaciones'] ?: null,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ];
            if ($this->citaId) {
                $this->citaService->update($this->citaId, $data);
                session()->flash('success', 'Cita actualizada');
            } else {
                $this->citaService->create($data);
                session()->flash('success', 'Cita creada');
            }
            $this->modalState['cita'] = false;
            $this->citaId = null;
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function cancelarCita($id)
    {
        try {
            $this->citaService->cancelar($id);
            session()->flash('success', 'Cita cancelada');
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function deleteCita()
    {
        try {
            $this->citaService->delete($this->citaId);
            session()->flash('success', 'Cita eliminada');
            $this->modalState['delete_cita'] = false;
            $this->citaId = null;
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    // ========== HELPERS ==========
    protected function mapEvaluacionToForm(EvaluacionMedidasNutricion $evaluacion): void
    {
        $this->evaluacionFormData = [
            'peso' => $evaluacion->peso ?? '',
            'estatura' => $evaluacion->estatura ?? '',
            'imc' => $evaluacion->imc ?? '',
            'porcentaje_grasa' => $evaluacion->porcentaje_grasa ?? '',
            'porcentaje_musculo' => $evaluacion->porcentaje_musculo ?? '',
            'masa_muscular' => $evaluacion->masa_muscular ?? '',
            'masa_grasa' => $evaluacion->masa_grasa ?? '',
            'masa_osea' => $evaluacion->masa_osea ?? '',
            'masa_residual' => $evaluacion->masa_residual ?? '',
            'circunferencias' => $evaluacion->circunferencias ?? [
                'estatura' => '', 'cuello' => '', 'brazo_normal' => '', 'brazo_contraido' => '',
                'torax' => '', 'cintura' => '', 'cintura_baja' => '', 'cadera' => '',
                'muslo' => '', 'gluteos' => '', 'pantorrilla' => '',
            ],
            'presion_arterial' => $evaluacion->presion_arterial ?? '',
            'frecuencia_cardiaca' => $evaluacion->frecuencia_cardiaca ?? '',
            'objetivo' => $evaluacion->objetivo ?? 'DEPORTES Ó SALUD',
            'nutricionista_id' => $evaluacion->nutricionista_id ?? '',
            'fecha_proxima_evaluacion' => $evaluacion->fecha_proxima_evaluacion ? $evaluacion->fecha_proxima_evaluacion->format('Y-m-d') : '',
            'estado' => $evaluacion->estado ?? 'completada',
            'observaciones' => $evaluacion->observaciones ?? '',
        ];
    }

    protected function mapEvaluacionFormToData(): array
    {
        return [
            'peso' => $this->evaluacionFormData['peso'] ?: null,
            'estatura' => $this->evaluacionFormData['estatura'] ?: null,
            'imc' => $this->evaluacionFormData['imc'] ?: null,
            'porcentaje_grasa' => $this->evaluacionFormData['porcentaje_grasa'] ?: null,
            'porcentaje_musculo' => $this->evaluacionFormData['porcentaje_musculo'] ?: null,
            'masa_muscular' => $this->evaluacionFormData['masa_muscular'] ?: null,
            'masa_grasa' => $this->evaluacionFormData['masa_grasa'] ?: null,
            'masa_osea' => $this->evaluacionFormData['masa_osea'] ?: null,
            'masa_residual' => $this->evaluacionFormData['masa_residual'] ?: null,
            'circunferencias' => $this->evaluacionFormData['circunferencias'],
            'presion_arterial' => $this->evaluacionFormData['presion_arterial'] ?: null,
            'frecuencia_cardiaca' => $this->evaluacionFormData['frecuencia_cardiaca'] ?: null,
            'objetivo' => $this->evaluacionFormData['objetivo'] ?: null,
            'nutricionista_id' => $this->evaluacionFormData['nutricionista_id'] ?: null,
            'fecha_proxima_evaluacion' => null,
            'estado' => 'completada',
            'observaciones' => $this->evaluacionFormData['observaciones'] ?: null,
        ];
    }

    protected function resetEvaluacionForm(): void
    {
        $this->evaluacionId = null;
        $this->evaluacionFormData = [
            'peso' => '',
            'estatura' => '',
            'imc' => '',
            'porcentaje_grasa' => '',
            'porcentaje_musculo' => '',
            'masa_muscular' => '',
            'masa_grasa' => '',
            'masa_osea' => '',
            'masa_residual' => '',
            'circunferencias' => [
                'estatura' => '', 'cuello' => '', 'brazo_normal' => '', 'brazo_contraido' => '',
                'torax' => '', 'cintura' => '', 'cintura_baja' => '', 'cadera' => '',
                'muslo' => '', 'gluteos' => '', 'pantorrilla' => '',
            ],
            'presion_arterial' => '',
            'frecuencia_cardiaca' => '',
            'objetivo' => 'DEPORTES Ó SALUD',
            'nutricionista_id' => '',
            'fecha_proxima_evaluacion' => '',
            'estado' => 'completada',
            'observaciones' => '',
        ];
    }

    protected function handleValidationErrors(\Illuminate\Validation\ValidationException $e): void
    {
        foreach ($e->errors() as $messages) {
            foreach ($messages as $message) {
                session()->flash('error', $message);
            }
        }
    }

    public function render()
    {
        $evaluaciones = collect([]);
        $ultimaEvaluacion = null;
        $seguimientos = collect([]);
        $citas = collect([]);

        if ($this->selectedClienteId) {
            // Evaluaciones (para ficha_salud)
            if ($this->mainTab === 'ficha_salud') {
                $filtros = $this->estadoFilter ? ['estado' => $this->estadoFilter] : [];
                $evaluaciones = $this->evaluacionService->getByCliente($this->selectedClienteId, $filtros, $this->perPage);
                $ultimaEvaluacion = $this->evaluacionService->getUltimaEvaluacion($this->selectedClienteId);
            }
            
            // Seguimientos nutricionales
            if ($this->mainTab === 'nutricion') {
                $filtros = array_filter([
                    'tipo' => $this->tipoFilter ?: null,
                    'estado' => $this->estadoFilter ?: null,
                ]);
                $seguimientos = $this->seguimientoService->getByCliente($this->selectedClienteId, $filtros, $this->perPage);
            }
            
            // Citas
            if ($this->mainTab === 'citas') {
                $filtros = array_filter([
                    'estado' => $this->estadoFilter ?: null,
                    'tipo' => $this->tipoFilter ?: null,
                ]);
                $citas = $this->citaService->getByCliente($this->selectedClienteId, $filtros, $this->perPage);
            }
        }

        $nutricionistas = \App\Models\User::role('nutricionista')->orderBy('name')->get();
        if ($nutricionistas->isEmpty()) {
            $nutricionistas = \App\Models\User::orderBy('name')->limit(20)->get();
        }

        $trainers = \App\Models\User::role('trainer')->orderBy('name')->get();
        
        $citasCliente = $this->selectedClienteId
            ? \App\Models\Core\Cita::where('cliente_id', $this->selectedClienteId)->orderBy('fecha_hora', 'desc')->limit(50)->get()
            : collect([]);

        return view('livewire.gestion-nutricional.gestion-nutricional-unificado-live', [
            'evaluaciones' => $evaluaciones,
            'ultimaEvaluacion' => $ultimaEvaluacion,
            'seguimientos' => $seguimientos,
            'citas' => $citas,
            'nutricionistas' => $nutricionistas,
            'trainers' => $trainers,
            'citasCliente' => $citasCliente,
        ]);
    }
}
