<?php

namespace iEducar\Packages\BuritiExportacaoUnificada\Console;

use iEducar\Packages\BuritiExportacaoUnificada\Services\EmissorDocumento;
use iEducar\Packages\BuritiExportacaoUnificada\Services\Exportador;
use iEducar\Packages\BuritiExportacaoUnificada\Support\Formato;
use Illuminate\Console\Command;

class ExportacaoDemoCommand extends Command
{
    protected $signature = 'exportacao:demo
        {--dir= : Diretório de saída (padrão: storage/app/exportacao-onda0)}
        {--formatos= : Lista separada por vírgula (padrão: todos do TR)}
        {--institucional : Emite PDF/HTML/TXT com layout + QR validador}';

    protected $description = 'Gera a prova da Onda 0 Laguna: um relatório em múltiplos formatos (TR 4.2/4.3).';

    public function handle(Exportador $exportador, EmissorDocumento $emissor): int
    {
        $dir = $this->option('dir')
            ?: storage_path('app/exportacao-onda0');

        $raw = $this->option('formatos');
        if (is_string($raw) && trim($raw) !== '') {
            $formatos = array_map(
                static fn (string $f): Formato => Formato::from(strtolower(trim($f))),
                explode(',', $raw),
            );
        } else {
            $formatos = Formato::todos();
        }

        $tabela = Exportador::amostraOnda0();

        $this->info('Onda 0 — exportação unificada');
        $this->line('Destino: '.$dir);

        $ok = 0;
        if ($this->option('institucional')) {
            $arquivos = $emissor->emitir(
                $tabela,
                $formatos,
                'Laguna PoC',
                'exportacao:demo',
                true,
                null,
                'prova-onda0-institucional',
            );
            foreach ($arquivos as $arquivo) {
                $caminho = $arquivo->gravar($dir);
                $this->line(sprintf(
                    '  ✓ %s (%d bytes, %s) [institucional]',
                    basename($caminho),
                    $arquivo->tamanho(),
                    $arquivo->mime(),
                ));
                $ok++;
            }
        } else {
            $arquivos = $exportador->exportarVarios($tabela, $formatos, 'prova-onda0-laguna');
            foreach ($arquivos as $arquivo) {
                $caminho = $arquivo->gravar($dir);
                $this->line(sprintf(
                    '  ✓ %s (%d bytes, %s)',
                    basename($caminho),
                    $arquivo->tamanho(),
                    $arquivo->mime(),
                ));
                $ok++;
            }
        }

        if ($ok < 3) {
            $this->error("Gate Onda 0 FALHOU: gerou {$ok} formato(s); mínimo 3.");

            return self::FAILURE;
        }

        $this->info("Gate Onda 0 OK: {$ok} formatos gerados.");

        return self::SUCCESS;
    }
}
