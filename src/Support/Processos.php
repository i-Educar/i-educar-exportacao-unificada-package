<?php

namespace iEducar\Packages\BuritiExportacaoUnificada\Support;

final class Processos
{
    public const int RAIZ = 881500;

    public const int CONFIG = 881501;

    public const int DEMO = 881502;

    /** @return array<int, string> */
    public static function titulos(): array
    {
        return [
            self::RAIZ => 'Exportação unificada',
            self::CONFIG => 'Configuração de exportação',
            self::DEMO => 'Demonstração de formatos',
        ];
    }
}
