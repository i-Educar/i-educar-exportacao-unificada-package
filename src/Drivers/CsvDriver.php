<?php

namespace iEducar\Packages\BuritiExportacaoUnificada\Drivers;

use iEducar\Packages\BuritiExportacaoUnificada\Support\Formato;
use iEducar\Packages\BuritiExportacaoUnificada\Support\TabelaExportacao;

final class CsvDriver implements DriverContrato
{
    public function formato(): Formato
    {
        return Formato::Csv;
    }

    public function gerar(TabelaExportacao $tabela): string
    {
        $fh = fopen('php://temp', 'r+');
        if ($fh === false) {
            throw new \RuntimeException('Falha ao abrir buffer CSV.');
        }

        fprintf($fh, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($fh, $tabela->cabecalhos, ';');
        foreach ($tabela->matriz() as $linha) {
            fputcsv($fh, $linha, ';');
        }
        rewind($fh);
        $out = stream_get_contents($fh) ?: '';
        fclose($fh);

        return $out;
    }
}
