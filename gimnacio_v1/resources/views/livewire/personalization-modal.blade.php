<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public bool $showModal = false;
    public string $appearance = 'system';
    public string $accent = 'neutral';
    public string $sidebar_bg = 'default';
    public string $header_bg = 'default';

    public function mount(): void
    {
        if (Auth::check()) {
            $user = Auth::user();
            $this->appearance = $user->appearance ?? 'system';
            $this->accent = $user->accent ?? 'neutral';
            $this->sidebar_bg = $user->sidebar_bg ?? 'default';
            $this->header_bg = $user->header_bg ?? 'default';
        }
    }

    public function openModal(): void
    {
        if (Auth::check()) {
            $user = Auth::user();
            $this->appearance = $user->appearance ?? 'system';
            $this->accent = $user->accent ?? 'neutral';
            $this->sidebar_bg = $user->sidebar_bg ?? 'default';
            $this->header_bg = $user->header_bg ?? 'default';
        }
        $this->showModal = true;
    }

    public function setAppearance(string $value): void
    {
        if (! in_array($value, ['light', 'dark', 'system'], true) || ! Auth::check()) return;
        Auth::user()->update(['appearance' => $value]);
        $this->appearance = $value;
        $this->dispatchAppearanceUpdated();
    }

    public function setAccent(string $value): void
    {
        if (! in_array($value, ['neutral', 'blue', 'green', 'red'], true) || ! Auth::check()) return;
        Auth::user()->update(['accent' => $value]);
        $this->accent = $value;
        $this->dispatchAppearanceUpdated();
    }

    public function setSidebarBg(string $value): void
    {
        if (! in_array($value, ['default', 'slate', 'blue', 'green', 'amber', 'red', 'violet', 'indigo'], true) || ! Auth::check()) return;
        Auth::user()->update(['sidebar_bg' => $value]);
        $this->sidebar_bg = $value;
        $this->dispatchAppearanceUpdated();
    }

    public function setHeaderBg(string $value): void
    {
        if (! in_array($value, ['default', 'slate', 'blue', 'green', 'amber', 'red', 'violet', 'indigo'], true) || ! Auth::check()) return;
        Auth::user()->update(['header_bg' => $value]);
        $this->header_bg = $value;
        $this->dispatchAppearanceUpdated();
    }

    private function dispatchAppearanceUpdated(): void
    {
        $user = Auth::user();
        $this->dispatch('appearance-updated',
            appearance: $user->appearance ?? 'system',
            accent: $user->accent ?? 'neutral',
            sidebar_bg: $this->sidebar_bg,
            header_bg: $this->header_bg
        );
    }
}; ?>

<div wire:key="personalization-modal">
    <flux:button size="sm" variant="ghost" class="w-full justify-start gap-2" wire:click="openModal" title="{{ __('Personalize') }}">
        <flux:icon name="paint-brush" class="size-4 shrink-0" />
        <span class="truncate">{{ __('Personalize') }}</span>
    </flux:button>

    <flux:modal name="personalization-modal" wire:model="showModal" focusable class="md:max-w-md">
        <flux:heading size="lg">{{ __('Personalize') }}</flux:heading>
        <flux:subheading>{{ __('Theme, sidebar and header colors. They adapt to light/dark mode.') }}</flux:subheading>

        <div class="mt-4 space-y-4">
            <div>
                <flux:text class="mb-2 block text-sm font-medium">{{ __('Theme mode') }}</flux:text>
                <div class="flex gap-1 rounded-lg border border-zinc-200 bg-zinc-50 p-1 dark:border-zinc-600 dark:bg-zinc-800">
                    <button type="button" wire:click="setAppearance('light')" class="min-w-0 flex-1 rounded-md px-2 py-2 text-xs transition @if($appearance === 'light') bg-white shadow dark:bg-zinc-700 @else hover:bg-zinc-200 dark:hover:bg-zinc-700 @endif" title="{{ __('Light') }}">
                        <flux:icon name="sun" class="mx-auto size-4" />
                    </button>
                    <button type="button" wire:click="setAppearance('dark')" class="min-w-0 flex-1 rounded-md px-2 py-2 text-xs transition @if($appearance === 'dark') bg-white shadow dark:bg-zinc-700 @else hover:bg-zinc-200 dark:hover:bg-zinc-700 @endif" title="{{ __('Dark') }}">
                        <flux:icon name="moon" class="mx-auto size-4" />
                    </button>
                    <button type="button" wire:click="setAppearance('system')" class="min-w-0 flex-1 rounded-md px-2 py-2 text-xs transition @if($appearance === 'system') bg-white shadow dark:bg-zinc-700 @else hover:bg-zinc-200 dark:hover:bg-zinc-700 @endif" title="{{ __('System') }}">
                        <flux:icon name="computer-desktop" class="mx-auto size-4" />
                    </button>
                </div>
            </div>

            <div>
                <flux:text class="mb-2 block text-sm font-medium">{{ __('Accent color') }}</flux:text>
                <div class="flex flex-wrap gap-2">
                    @foreach(['neutral' => 'bg-zinc-400', 'blue' => 'bg-blue-500', 'green' => 'bg-green-500', 'red' => 'bg-red-500'] as $val => $dotClass)
                        <button type="button" wire:click="setAccent('{{ $val }}')" class="flex items-center gap-2 rounded-lg border px-3 py-2 text-sm transition @if($accent === $val) border-accent ring-2 ring-accent @else border-zinc-200 hover:border-zinc-300 dark:border-zinc-600 dark:hover:border-zinc-500 @endif">
                            <span class="size-4 rounded-full {{ $dotClass }}"></span>
                            <span>{{ __(ucfirst($val)) }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <div>
                <flux:text class="mb-2 block text-sm font-medium">{{ __('Sidebar background') }}</flux:text>
                <div class="flex flex-wrap gap-2">
                    @foreach(['default' => 'border-zinc-300 bg-zinc-100 dark:bg-zinc-600', 'slate' => 'bg-slate-500', 'blue' => 'bg-blue-500', 'green' => 'bg-green-500', 'amber' => 'bg-amber-500', 'red' => 'bg-red-500', 'violet' => 'bg-violet-500', 'indigo' => 'bg-indigo-500'] as $val => $swatchClass)
                        <button type="button" wire:click="setSidebarBg('{{ $val }}')" class="rounded-lg border-2 p-1.5 transition @if($sidebar_bg === $val) border-accent ring-2 ring-accent @else border-transparent hover:border-zinc-300 dark:hover:border-zinc-600 @endif" title="{{ __(ucfirst($val)) }}">
                            <span class="block size-6 rounded-full {{ $swatchClass }}"></span>
                        </button>
                    @endforeach
                </div>
            </div>

            <div>
                <flux:text class="mb-2 block text-sm font-medium">{{ __('Header background') }}</flux:text>
                <div class="flex flex-wrap gap-2">
                    @foreach(['default' => 'border-zinc-300 bg-zinc-100 dark:bg-zinc-600', 'slate' => 'bg-slate-500', 'blue' => 'bg-blue-500', 'green' => 'bg-green-500', 'amber' => 'bg-amber-500', 'red' => 'bg-red-500', 'violet' => 'bg-violet-500', 'indigo' => 'bg-indigo-500'] as $val => $swatchClass)
                        <button type="button" wire:click="setHeaderBg('{{ $val }}')" class="rounded-lg border-2 p-1.5 transition @if($header_bg === $val) border-accent ring-2 ring-accent @else border-transparent hover:border-zinc-300 dark:hover:border-zinc-600 @endif" title="{{ __(ucfirst($val)) }}">
                            <span class="block size-6 rounded-full {{ $swatchClass }}"></span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <flux:modal.close class="mt-4">
            <flux:button variant="primary">{{ __('Close') }}</flux:button>
        </flux:modal.close>
    </flux:modal>
</div>
