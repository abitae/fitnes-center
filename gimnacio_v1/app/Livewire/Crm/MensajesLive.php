<?php

namespace App\Livewire\Crm;

use App\Services\ClienteService;
use App\Services\CrmMensajeService;
use Livewire\Component;
use Livewire\WithPagination;

class MensajesLive extends Component
{
    use WithPagination;

    public $clienteSearch = '';
    public $clientes;
    public $selectedClienteId = null;
    public $selectedCliente = null;
    public $isSearching = false;
    public $contenido = '';
    public $canalFilter = '';
    public $perPage = 15;

    protected $paginationTheme = 'tailwind';

    protected CrmMensajeService $crmService;
    protected ClienteService $clienteService;

    public function boot(CrmMensajeService $crmService, ClienteService $clienteService)
    {
        $this->crmService = $crmService;
        $this->clienteService = $clienteService;
    }

    public function mount()
    {
        $this->clientes = collect([]);
    }

    public function updatingClienteSearch()
    {
        $this->isSearching = true;
    }

    public function updatedClienteSearch()
    {
        $term = trim($this->clienteSearch);
        $this->clientes = strlen($term) >= 2 ? $this->clienteService->quickSearch($term, 10) : collect([]);
        $this->isSearching = false;
    }

    public function selectCliente($id)
    {
        $this->selectedClienteId = $id;
        $this->selectedCliente = $this->clienteService->find($id);
        $this->clienteSearch = $this->selectedCliente->nombres . ' ' . $this->selectedCliente->apellidos;
        $this->clientes = collect([]);
        $this->resetPage();
    }

    public function clearCliente()
    {
        $this->selectedClienteId = null;
        $this->selectedCliente = null;
        $this->clienteSearch = '';
        $this->clientes = collect([]);
        $this->contenido = '';
        $this->resetPage();
    }

    public function enviarWhatsApp()
    {
        try {
            if (! $this->selectedClienteId) {
                session()->flash('error', 'Selecciona un cliente');
                return;
            }
            if (empty(trim($this->contenido))) {
                session()->flash('error', 'Escribe el mensaje');
                return;
            }
            $this->crmService->enviarWhatsApp($this->selectedClienteId, trim($this->contenido), auth()->id());
            session()->flash('success', 'Mensaje enviado por WhatsApp');
            $this->contenido = '';
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $mensajes = $this->crmService->getByCliente($this->selectedClienteId, array_filter(['canal' => $this->canalFilter ?: null]), $this->perPage);

        return view('livewire.crm.mensajes-live', ['mensajes' => $mensajes]);
    }
}
