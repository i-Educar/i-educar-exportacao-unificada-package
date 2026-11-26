<?php

namespace iEducar\Packages\BuritiExportacaoUnificada\Providers;

use iEducar\Packages\BuritiExportacaoUnificada\Console\ExportacaoDemoCommand;
use iEducar\Packages\BuritiExportacaoUnificada\Console\ExportacaoDemoCriticosCommand;
use iEducar\Packages\BuritiExportacaoUnificada\Http\Controllers\ExportacaoController;
use iEducar\Packages\BuritiExportacaoUnificada\Services\EmissorDocumento;
use iEducar\Packages\BuritiExportacaoUnificada\Services\Exportador;
use iEducar\Packages\BuritiExportacaoUnificada\Services\LayoutInstitucional;
use iEducar\Packages\BuritiExportacaoUnificada\Services\ValidadorDocumentoBridge;
use iEducar\Packages\BuritiExportacaoUnificada\Support\Identidade;
use iEducar\Packages\BuritiExportacaoUnificada\Support\MenuFluxo;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ExportacaoUnificadaServiceProvider extends ServiceProvider
{
    public const string PACKAGE = 'buriti/i-educar-exportacao-unificada-package';

    public function register(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__, 2).'/config/exportacao.php', 'exportacao');

        $this->app->singleton(Exportador::class, static fn (): Exportador => new Exportador());
        $this->app->singleton(LayoutInstitucional::class, static fn (): LayoutInstitucional => new LayoutInstitucional());
        $this->app->singleton(ValidadorDocumentoBridge::class, static fn (): ValidadorDocumentoBridge => new ValidadorDocumentoBridge());
        $this->app->singleton(EmissorDocumento::class, static function ($app): EmissorDocumento {
            return new EmissorDocumento(
                $app->make(Exportador::class),
                $app->make(LayoutInstitucional::class),
                $app->make(ValidadorDocumentoBridge::class),
            );
        });

        // Fora de runningInConsole(): o check do Ambiente Laguna (HTTP) usa Artisan::all().
        $this->commands([ExportacaoDemoCommand::class, ExportacaoDemoCriticosCommand::class]);
    }

    public function boot(): void
    {
        if (class_exists(\iEducar\Packages\BuritiLagunaAmbiente\Support\BuritiUi::class)) {
            \iEducar\Packages\BuritiLagunaAmbiente\Support\BuritiUi::registrar();
        }

        $root = dirname(__DIR__, 2);
        $this->loadViewsFrom($root.'/resources/views', 'exportacao');

        if ($this->app->runningInConsole()) {
            if (\App\Support\Tenancy\PackageTenant::allows(self::PACKAGE)) {
                $this->loadMigrationsFrom($root.'/database/migrations');
            }
        }

        // Validação pública (QR / link impresso) — sem auth.
        Route::middleware(['web'])->prefix('exportacao')->group(function (): void {
            Route::get('/validar', [ExportacaoController::class, 'validarForm']);
            Route::get('/validar/{codigo}', [ExportacaoController::class, 'validar'])->where('codigo', '[A-Za-z0-9]+');
            Route::post('/validar', [ExportacaoController::class, 'validar']);
        });

        Route::middleware([
            'web',
            'ieducar.navigation',
            'ieducar.footer',
            'ieducar.xssbypass',
            'ieducar.suspended',
            'auth',
            'ieducar.checkresetpassword',
        ])->group($root.'/routes/web.php');

        View::composer('exportacao::*', function ($view): void {
            $view->with('identidade', Identidade::tokens());
            $view->with('menuGrupos', MenuFluxo::grupos());
        });
    }
}
