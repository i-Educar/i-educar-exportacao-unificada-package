<?php

namespace iEducar\Packages\BuritiExportacaoUnificada\Support;

final class Identidade
{
    public const COR = '#0a7a8a';

    public const DESTAQUE = '#1495a8';

    public const COR_CLARA = '#e6f5f7';

    public const ICONE = 'fa-share-square-o';

    public const NOME = 'Exportação';

    public const DESCRICAO = 'PDF, Excel e formatos do edital com layout institucional e QR de validação';

    /** @return array{cor:string,destaque:string,clara:string,icone:string,nome:string,descricao:string} */
    public static function tokens(): array
    {
        return [
            'cor' => self::COR,
            'destaque' => self::DESTAQUE,
            'clara' => self::COR_CLARA,
            'icone' => self::ICONE,
            'nome' => self::NOME,
            'descricao' => self::DESCRICAO,
        ];
    }
}
