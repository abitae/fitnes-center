<?php

namespace App\Livewire\Categorias;

use App\Services\CategoriaProductoService;
use Livewire\Component;
use Livewire\WithPagination;

class CategoriaProductoLive extends Component
{
    use WithPagination;

    public $search = '';
    public $estadoFilter = '';
    public $perPage = 15;

    public $modalState = ['create' => false, 'delete' => false];
    public $categoriaId = null;

    public $formData = [
        'nombre' => '',
        'descripcion' => '',
        'estado' => 'activa',
    ];

    protected $paginationTheme = 'tailwind';
    protected CategoriaProductoService $service;

    public function boot(CategoriaProductoService $service)
    {
        $this->service = $service;
    }

    public function mount()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->modalState['create'] = true;
    }

    public function openEditModal($id)
    {
        $categoria = $this->service->find($id);
        if (!$categoria) {
            session()->flash('error', 'Categoría no encontrada');
            return;
        }

        $this->categoriaId = $categoria->id;
        $this->formData = [
            'nombre' => $categoria->nombre,
            'descripcion' => $categoria->descripcion ?? '',
            'estado' => $categoria->estado,
        ];
        $this->modalState['create'] = true;
    }

    public function openDeleteModal($id)
    {
        $this->categoriaId = $id;
        $this->modalState['delete'] = true;
    }

    public function save()
    {
        try {
            if ($this->categoriaId) {
                $this->service->update($this->categoriaId, $this->formData);
                session()->flash('success', 'Categoría actualizada exitosamente.');
            } else {
                $this->service->create($this->formData);
                session()->flash('success', 'Categoría creada exitosamente.');
            }
            $this->closeModal();
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function delete()
    {
        try {
            $this->service->delete($this->categoriaId);
            session()->flash('success', 'Categoría eliminada exitosamente.');
            $this->closeModal();
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function closeModal()
    {
        $this->modalState = ['create' => false, 'delete' => false];
        $this->categoriaId = null;
        $this->resetForm();
    }

    protected function resetForm()
    {
        $this->formData = [
            'nombre' => '',
            'descripcion' => '',
            'estado' => 'activa',
        ];
    }

    public function render()
    {
        $filtros = [];
        if ($this->search) {
            $filtros['busqueda'] = $this->search;
        }
        if ($this->estadoFilter) {
            $filtros['estado'] = $this->estadoFilter;
        }

        $categorias = $this->service->obtenerCategorias($this->perPage, $filtros);

        return view('livewire.categorias.categoria-producto-live', [
            'categorias' => $categorias,
        ]);
    }
}
