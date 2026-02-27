<?php

namespace App\Providers;

use App\Services\WhatsApp\MockWhatsAppService;
use App\Services\WhatsApp\WhatsAppServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(WhatsAppServiceInterface::class, MockWhatsAppService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $sidebarBgClasses = [
            'default' => 'bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700',
            'slate' => 'bg-slate-50 dark:bg-slate-950 border-r border-slate-200 dark:border-slate-800',
            'blue' => 'bg-blue-50 dark:bg-blue-950 border-r border-blue-200 dark:border-blue-800',
            'green' => 'bg-green-50 dark:bg-green-950 border-r border-green-200 dark:border-green-800',
            'amber' => 'bg-amber-50 dark:bg-amber-950 border-r border-amber-200 dark:border-amber-800',
            'red' => 'bg-red-50 dark:bg-red-950 border-r border-red-200 dark:border-red-800',
            'violet' => 'bg-violet-50 dark:bg-violet-950 border-r border-violet-200 dark:border-violet-800',
            'indigo' => 'bg-indigo-50 dark:bg-indigo-950 border-r border-indigo-200 dark:border-indigo-800',
        ];
        $headerBgClasses = [
            'default' => 'bg-white lg:bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700',
            'slate' => 'bg-white lg:bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800',
            'blue' => 'bg-white lg:bg-blue-50 dark:bg-blue-950 border-b border-blue-200 dark:border-blue-800',
            'green' => 'bg-white lg:bg-green-50 dark:bg-green-950 border-b border-green-200 dark:border-green-800',
            'amber' => 'bg-white lg:bg-amber-50 dark:bg-amber-950 border-b border-amber-200 dark:border-amber-800',
            'red' => 'bg-white lg:bg-red-50 dark:bg-red-950 border-b border-red-200 dark:border-red-800',
            'violet' => 'bg-white lg:bg-violet-50 dark:bg-violet-950 border-b border-violet-200 dark:border-violet-800',
            'indigo' => 'bg-white lg:bg-indigo-50 dark:bg-indigo-950 border-b border-indigo-200 dark:border-indigo-800',
        ];

        View::composer('components.layouts.app.sidebar', function ($view) use ($sidebarBgClasses, $headerBgClasses) {
            $appearanceClass = 'dark';
            $appearanceValue = 'system';
            $accentClass = 'accent-neutral';
            $sidebarBgClass = $sidebarBgClasses['default'];
            $headerBgClass = $headerBgClasses['default'];
            $accentValue = 'neutral';
            $sidebarBgValue = 'default';
            $headerBgValue = 'default';

            if (Auth::check()) {
                $user = Auth::user();
                $pref = $user->appearance ?? 'system';
                $appearanceValue = $pref;
                $appearanceClass = $pref === 'system' ? 'dark' : $pref;
                $accentValue = $user->accent ?? 'neutral';
                $accentClass = 'accent-' . $accentValue;
                $sidebarBgValue = $user->sidebar_bg ?? 'default';
                $headerBgValue = $user->header_bg ?? 'default';
                $sidebarBgClass = $sidebarBgClasses[$sidebarBgValue] ?? $sidebarBgClasses['default'];
                $headerBgClass = $headerBgClasses[$headerBgValue] ?? $headerBgClasses['default'];
            }

            $view->with('appearanceClass', $appearanceClass);
            $view->with('appearanceValue', $appearanceValue);
            $view->with('accentClass', $accentClass);
            $view->with('accentValue', $accentValue);
            $view->with('sidebarBgClass', $sidebarBgClass);
            $view->with('headerBgClass', $headerBgClass);
            $view->with('sidebarBgValue', $sidebarBgValue);
            $view->with('headerBgValue', $headerBgValue);
        });
    }
}
