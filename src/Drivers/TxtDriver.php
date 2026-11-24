<?php

namespace iEducar\Packages\BuritiExportacaoUnificada\Drivers;

use iEducar\Packages\BuritiExportacaoUnificada\Support\Formato;
use iEducar\Packages\BuritiExportacaoUnificada\Support\TabelaExportacao;

final class TxtDriver implements DriverContrato
{
    public function formato(): Formato
    {
        return Formato::Txt;
    }

    public function gerar(TabelaExportacao $tabela): string
    {
        $linhas = [$tabela->titulo];
        if ($tabela->subtitulo !== '') {
            $linhas[] = $tabela->subtitulo;
        }
        $linhas[] = '';
        $linhas[] = implode("\t", $tabela->cabecalhos);
        foreach ($tabela->matriz() as $linha) {
            $linhas[] = implode("\t", $linha);
        }

        return implode("\n", $linhas) . "\n";
    }
}
