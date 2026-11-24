<?php

namespace iEducar\Packages\BuritiExportacaoUnificada\Support;

final class ArquivoExportado
{
    public function __construct(
        public readonly string $conteudo,
        public readonly Formato $formato,
        public readonly string $nomeBase,
    ) {
    }

    public function nomeArquivo(): string
    {
        $base = preg_replace('/[^a-zA-Z0-9_\-]+/', '_', $this->nomeBase) ?: 'exportacao';

        return $base . '.' . $this->formato->extensao();
    }

    public function mime(): string
    {
        return $this->formato->mime();
    }

    public function tamanho(): int
    {
        return strlen($this->conteudo);
    }

    public function gravar(string $diretorio): string
    {
        if (!is_dir($diretorio) && !mkdir($diretorio, 0775, true) && !is_dir($diretorio)) {
            throw new \RuntimeException("Não foi possível criar o diretório {$diretorio}");
        }

        $caminho = rtrim($diretorio, '/') . '/' . $this->nomeArquivo();
        if (file_put_contents($caminho, $this->conteudo) === false) {
            throw new \RuntimeException("Falha ao gravar {$caminho}");
        }

        return $caminho;
    }
}
