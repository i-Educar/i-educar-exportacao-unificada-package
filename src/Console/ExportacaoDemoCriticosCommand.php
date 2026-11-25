<?php

namespace iEducar\Packages\BuritiExportacaoUnificada\Console;

use iEducar\Packages\BuritiExportacaoUnificada\Services\Exportador;
use iEducar\Packages\BuritiExportacaoUnificada\Support\Formato;
use iEducar\Packages\BuritiExportacaoUnificada\Support\TabelaExportacao;
use Illuminate\Console\Command;

/**
 * Onda 4.2 — liga exportação 4.x a relatórios críticos da demonstração.
 */
class ExportacaoDemoCriticosCommand extends Command
{
    protected $signature = 'exportacao:demo-criticos
        {--dir= : Diretório de saída}
        {--formatos=pdf,csv,xlsx : Formatos TR para a demo}';

    protected $description = 'Onda 4 — exporta relatórios críticos (custo mensal + resumo passageiros)';

    public function handle(Exportador $exportador): int
    {
        $dir = $this->option('dir') ?: storage_path('app/exportacao-onda4-criticos');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $raw = (string) $this->option('formatos');
        $formatos = array_map(
            static fn (string $f): Formato => Formato::from(strtolower(trim($f))),
            array_filter(explode(',', $raw)),
        );
        if ($formatos === []) {
            $formatos = [Formato::from('pdf'), Formato::from('csv'), Formato::from('xlsx')];
        }

        $relatorios = [
            'transporte-custo-mensal' => new TabelaExportacao(
                'Custo mensal / PNATE — Laguna PoC',
                ['Fonte', 'Categoria', 'Valor (R$)', 'Mês/Ano'],
                [
                    ['PNATE', 'Combustível', 1500.50, date('m/Y')],
                    ['PNATE', 'Manutenção', 820.00, date('m/Y')],
                    ['Próprio', 'Combustível', 300.00, date('m/Y')],
                ],
                'Relatório crítico demo Onda 4 (TR 4.x × 5.4)',
            ),
            'transporte-resumo-passageiros' => new TabelaExportacao(
                'Resumo frota / passageiros — Laguna PoC',
                ['Rota', 'Veículo', 'Passageiros', 'Km dia'],
                [
                    ['Rota Centro', 'BUS-01', 42, 38],
                    ['Rota Norte', 'BUS-02', 35, 51],
                    ['Rota Rural', 'VAN-03', 18, 62],
                ],
                'Relatório crítico demo Onda 4',
            ),
        ];

        $this->info('Onda 4 — exportação relatórios críticos');
        $this->line('Destino: '.$dir);

        $ok = 0;
        foreach ($relatorios as $nome => $tabela) {
            foreach ($exportador->exportarVarios($tabela, $formatos, $nome) as $arquivo) {
                $caminho = $arquivo->gravar($dir);
                $this->line(sprintf('  ✓ %s (%d bytes)', basename($caminho), $arquivo->tamanho()));
                $ok++;
            }
        }

        if ($ok < 3) {
            $this->error("Gate Onda 4.2 FALHOU: gerou {$ok} arquivo(s); mínimo 3.");

            return self::FAILURE;
        }

        $this->info("Gate Onda 4.2 OK: {$ok} arquivos (2 relatórios × formatos).");

        return self::SUCCESS;
    }
}
