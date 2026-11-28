<?php

return [
    'public' => [
        'aparecer_na_home' => true,
        'titulo' => 'Exportação',
        'ordem' => 12,
        'url' => '/exportacao',
    ],

    /*
    |--------------------------------------------------------------------------
    | Layout institucional dos documentos emitidos
    |--------------------------------------------------------------------------
    | Usado por todos os módulos Buriti (exceto Relatórios avançados).
    */
    'institucional' => [
        'entidade' => env(
            'EXPORTACAO_ENTIDADE',
            (string) (env('APP_NAME') ?: 'Prefeitura Municipal')
        ),
        'secretaria' => env('EXPORTACAO_SECRETARIA', 'Secretaria Municipal de Educação'),
        'sistema' => env('EXPORTACAO_SISTEMA', 'i-Educar'),
        'logo_esquerda' => env('EXPORTACAO_LOGO_ESQ', ''),
        'logo_direita' => env('EXPORTACAO_LOGO_DIR', ''),
        'rodape_extra' => env('EXPORTACAO_RODAPE', 'Documento gerado eletronicamente — exportação unificada TR 4.2/4.3'),
        'cor' => env('EXPORTACAO_COR', '#0a7a8a'),
        'mostrar_assinaturas' => (bool) env('EXPORTACAO_ASSINATURAS', true),
        'cargos_assinatura' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env(
                'EXPORTACAO_CARGOS_ASSINATURA',
                'Responsável técnico,Gestor(a) da unidade'
            ))
        ))),
    ],

    /*
    |--------------------------------------------------------------------------
    | Assinatura digital (LibreSign) nos documentos emitidos
    |--------------------------------------------------------------------------
    | Quando ativo e o pacote assinatura-digital estiver instalado, todo PDF
    | com 1..N assinaturas entra no fluxo LibreSign após a emissão.
    */
    'assinatura_digital' => [
        'habilitado' => (bool) env('EXPORTACAO_LIBRESIGN', true),
        'falhar_silencioso' => (bool) env('EXPORTACAO_LIBRESIGN_SOFT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Validador de documentos (QR + link público)
    |--------------------------------------------------------------------------
    | Conecta ao pacote Relatórios avançados quando disponível.
    | Fallback: rota própria /exportacao/validar/{code}.
    */
    'validacao' => [
        'habilitado' => (bool) env('EXPORTACAO_VALIDACAO', true),
        'preferir_advanced_reports' => (bool) env('EXPORTACAO_VALIDACAO_AR', true),
        'tipo_documento' => env('EXPORTACAO_VALIDACAO_TIPO', 'exportacao_buriti'),
    ],

    'formatos_padrao' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('EXPORTACAO_FORMATOS', 'pdf,xlsx,csv,html'))
    ))),
];
