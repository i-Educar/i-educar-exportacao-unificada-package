<?php

use App\Menu;
use App\Services\MenuCacheService;
use iEducar\Packages\BuritiExportacaoUnificada\Support\Processos;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('menus') || ! class_exists(Menu::class)) {
            return;
        }

        $pai = Menu::query()
            ->whereNull('parent_id')
            ->where(function ($q): void {
                $q->where('process', 25)->orWhere('title', 'Configurações');
            })
            ->first();

        if ($pai === null) {
            $pai = Menu::query()->whereNull('parent_id')->orderBy('order')->first();
        }

        if ($pai === null) {
            return;
        }

        $raiz = Menu::query()->updateOrCreate(['process' => Processos::RAIZ], [
            'parent_id' => $pai->getKey(),
            'title' => 'Exportação unificada',
            'description' => 'Emissão multi-formato e validação de documentos (TR 4.x)',
            'link' => '/exportacao',
            'icon' => 'fa-share-square-o',
            'order' => 6,
            'type' => 2,
            'active' => true,
        ]);

        $links = [
            [Processos::CONFIG, 'Painel / configuração', '/exportacao', 'Status e configuração do motor', 1],
            [Processos::DEMO, 'Demonstração de formatos', '/exportacao/demo', 'Prova TR 4.2/4.3', 2],
        ];

        foreach ($links as [$processo, $titulo, $link, $descricao, $ordem]) {
            Menu::query()->updateOrCreate(['process' => $processo], [
                'parent_id' => $raiz->getKey(),
                'title' => $titulo,
                'description' => $descricao,
                'link' => $link,
                'order' => $ordem,
                'type' => 3,
                'active' => true,
            ]);
        }

        $processos = [Processos::RAIZ, Processos::CONFIG, Processos::DEMO];
        if (Schema::hasTable('pmieducar.tipo_usuario') && Schema::hasTable('pmieducar.menu_tipo_usuario')) {
            foreach (DB::table('pmieducar.tipo_usuario')->where('ativo', 1)->get(['cod_tipo_usuario']) as $tipo) {
                foreach ($processos as $processo) {
                    $menuId = Menu::query()->where('process', $processo)->value('id');
                    if ($menuId === null) {
                        continue;
                    }
                    DB::table('pmieducar.menu_tipo_usuario')->updateOrInsert(
                        ['ref_cod_tipo_usuario' => $tipo->cod_tipo_usuario, 'menu_id' => $menuId],
                        ['cadastra' => 1, 'visualiza' => 1, 'exclui' => 0],
                    );
                }
            }
        }

        if (class_exists(MenuCacheService::class)) {
            try {
                app(MenuCacheService::class)->flushAll();
            } catch (\Throwable) {
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('menus') || ! class_exists(Menu::class)) {
            return;
        }

        $raiz = Menu::query()->where('process', Processos::RAIZ)->first();
        if ($raiz === null) {
            return;
        }

        $folhas = Menu::query()->where('parent_id', $raiz->getKey())->pluck('id');
        $ids = $folhas->push($raiz->getKey());

        if (Schema::hasTable('pmieducar.menu_tipo_usuario')) {
            DB::table('pmieducar.menu_tipo_usuario')->whereIn('menu_id', $ids)->delete();
        }

        Menu::query()->whereIn('id', $folhas)->delete();
        $raiz->delete();

        if (class_exists(MenuCacheService::class)) {
            try {
                app(MenuCacheService::class)->flushAll();
            } catch (\Throwable) {
            }
        }
    }
};
