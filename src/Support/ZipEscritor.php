<?php

namespace iEducar\Packages\BuritiExportacaoUnificada\Support;

final class ZipEscritor
{
    /** @param array<string, string> $arquivos caminho => conteúdo */
    public static function montar(array $arquivos): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'expzip');
        if ($tmp === false) {
            throw new \RuntimeException('tempnam falhou.');
        }
        $zipPath = $tmp . '.zip';
        @unlink($tmp);

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Não foi possível criar ZIP.');
        }
        foreach ($arquivos as $nome => $conteudo) {
            $zip->addFromString($nome, $conteudo);
        }
        $zip->close();
        $bin = file_get_contents($zipPath);
        @unlink($zipPath);
        if ($bin === false) {
            throw new \RuntimeException('Falha ao ler ZIP.');
        }

        return $bin;
    }
}
