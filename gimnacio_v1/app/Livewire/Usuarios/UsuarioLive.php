<?php

namespace App\Livewire\Usuarios;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Livewire\WithPagination;

class UsuarioLive extends Component
{
    use WithPagination;

    public $search = '';
    public $roleFilter = '';
    public $perPage = 15;

    public $modalState = [
        'form' => false,
        'delete' => false,
    ];

    public $userId = null;
    public $formData = [
        'name' => '',
        'email' => '',
        'password' => '',
        'password_confirmation' => '',
        'estado' => 'activo',
        'role' => '',
    ];

    protected $paginationTheme = 'tailwind';

    public function mount()
    {
        if (! Auth::user()->hasAnyRole(['super_administrador', 'administrador'])) {
            abort(403);
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->modalState['form'] = true;
    }

    public function openEditModal($id)
    {
        $user = User::with('roles')->find($id);
        if (! $user) {
            session()->flash('error', 'Usuario no encontrado');
            return;
        }
        $this->userId = $user->id;
        $this->formData = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => '',
            'password_confirmation' => '',
            'estado' => $user->estado ?? 'activo',
            'role' => $user->roles->first()?->name ?? '',
        ];
        $this->modalState['form'] = true;
    }

    public function openDeleteModal($id)
    {
        $this->userId = $id;
        $this->modalState['delete'] = true;
    }

    public function save()
    {
        $rules = [
            'formData.name' => 'required|string|max:255',
            'formData.email' => 'required|email|unique:users,email,' . ($this->userId ?? 'NULL'),
            'formData.estado' => 'required|in:activo,inactivo',
            'formData.role' => 'required|exists:roles,name',
        ];
        if ($this->userId) {
            $rules['formData.password'] = ['nullable', 'string', Password::defaults()];
        } else {
            $rules['formData.password'] = ['required', 'string', 'confirmed', Password::defaults()];
        }

        $this->validate($rules);

        try {
            if ($this->userId) {
                $user = User::findOrFail($this->userId);
                $user->name = $this->formData['name'];
                $user->email = $this->formData['email'];
                $user->estado = $this->formData['estado'];
                if (! empty($this->formData['password'])) {
                    $user->password = Hash::make($this->formData['password']);
                }
                $user->save();
                $user->syncRoles([$this->formData['role']]);
                session()->flash('success', 'Usuario actualizado correctamente');
            } else {
                $user = User::create([
                    'name' => $this->formData['name'],
                    'email' => $this->formData['email'],
                    'password' => Hash::make($this->formData['password']),
                    'estado' => $this->formData['estado'],
                ]);
                $user->syncRoles([$this->formData['role']]);
                session()->flash('success', 'Usuario creado correctamente');
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
            $user = User::findOrFail($this->userId);
            if ($user->id === Auth::user()->id) {
                session()->flash('error', 'No puedes eliminar tu propio usuario.');
                return;
            }
            $user->delete();
            session()->flash('success', 'Usuario eliminado correctamente.');
            $this->closeModal();
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function closeModal()
    {
        $this->modalState = ['form' => false, 'delete' => false];
        $this->userId = null;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->formData = [
            'name' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
            'estado' => 'activo',
            'role' => '',
        ];
    }

    public function render()
    {
        $query = User::query()->with('roles');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->roleFilter) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $this->roleFilter));
        }

        $usuarios = $query->orderBy('name')->paginate($this->perPage);
        $roles = \Spatie\Permission\Models\Role::orderBy('name')->get();

        return view('livewire.usuarios.usuario-live', [
            'usuarios' => $usuarios,
            'roles' => $roles,
        ]);
    }
}
