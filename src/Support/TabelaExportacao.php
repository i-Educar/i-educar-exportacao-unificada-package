<?php

namespace iEducar\Packages\BuritiExportacaoUnificada\Support;

final class TabelaExportacao
{
    /**
     * @param list<string> $cabecalhos
     * @param list<list<string|int|float|null>> $linhas
     */
    public function __construct(
        public readonly string $titulo,
        public readonly array $cabecalhos,
        public readonly array $linhas,
        public readonly string $subtitulo = '',
    ) {
        if ($this->cabecalhos === []) {
            throw new \InvalidArgumentException('A tabela precisa de cabeçalhos.');
        }
    }

    /** @return list<list<string>> */
    public function matriz(): array
    {
        $saida = [];
        foreach ($this->linhas as $linha) {
            $cells = [];
            foreach ($this->cabecalhos as $i => $_) {
                $cells[] = $this->texto($linha[$i] ?? '');
            }
            $saida[] = $cells;
        }

        return $saida;
    }

    private function texto(mixed $valor): string
    {
        if ($valor === null) {
            return '';
        }

        if (is_scalar($valor)) {
            return (string) $valor;
        }

        return json_encode($valor, JSON_UNESCAPED_UNICODE) ?: '';
    }
}
