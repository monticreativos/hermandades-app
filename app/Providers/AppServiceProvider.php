<?php

namespace App\Providers;

use App\Listeners\AuditarIntentoLoginFallido;
use App\Listeners\AuditarLoginExitoso;
use App\Listeners\AuditarLogoutUsuario;
use App\Models\AvisoHermano;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(Login::class, AuditarLoginExitoso::class);
        Event::listen(Logout::class, AuditarLogoutUsuario::class);
        Event::listen(Failed::class, AuditarIntentoLoginFallido::class);

        View::composer('layouts.portal', function ($view): void {
            if (! Auth::guard('portal')->check()) {
                $view->with('portalAvisosUrgentesSinLeer', 0);

                return;
            }
            $hermano = Auth::guard('portal')->user()->hermano;
            $n = AvisoHermano::query()
                ->where('hermano_id', $hermano->id)
                ->whereNull('leido_en')
                ->whereHas('aviso', fn ($q) => $q->where('urgente', true)->whereNotNull('enviado_en'))
                ->count();
            $view->with('portalAvisosUrgentesSinLeer', $n);
        });
    }
}
