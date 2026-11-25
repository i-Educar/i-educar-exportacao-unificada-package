<?php

namespace iEducar\Packages\BuritiExportacaoUnificada\Providers;

use iEducar\Packages\BuritiExportacaoUnificada\Console\ExportacaoDemoCommand;
use iEducar\Packages\BuritiExportacaoUnificada\Console\ExportacaoDemoCriticosCommand;
use iEducar\Packages\BuritiExportacaoUnificada\Services\Exportador;
use Illuminate\Support\ServiceProvider;

class ExportacaoUnificadaServiceProvider extends ServiceProvider
{
    public const string PACKAGE = 'buriti/i-educar-exportacao-unificada-package';

    public function register(): void
    {
        $this->app->singleton(Exportador::class, static fn (): Exportador => new Exportador());
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([ExportacaoDemoCommand::class, ExportacaoDemoCriticosCommand::class]);
        }
    }
}
