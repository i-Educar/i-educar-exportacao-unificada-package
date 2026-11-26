<?php

namespace iEducar\Packages\BuritiExportacaoUnificada\Support;

final class MenuFluxo
{
    /**
     * @return list<array{nome:string,itens:list<array{rotulo:string,url:string,icone:string,chave:string}>}>
     */
    public static function grupos(): array
    {
        return [
            [
                'nome' => 'Exportação',
                'itens' => [
                    [
                        'chave' => 'painel',
                        'rotulo' => 'Painel / configuração',
                        'url' => '/exportacao',
                        'icone' => 'fa-cog',
                    ],
                    [
                        'chave' => 'demo',
                        'rotulo' => 'Demonstração TR 4.2/4.3',
                        'url' => '/exportacao/demo',
                        'icone' => 'fa-file-text-o',
                    ],
                    [
                        'chave' => 'validar',
                        'rotulo' => 'Validar documento',
                        'url' => '/exportacao/validar',
                        'icone' => 'fa-qrcode',
                    ],
                ],
            ],
        ];
    }
}
