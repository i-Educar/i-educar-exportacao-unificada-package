@extends('exportacao::layout')
@section('titulo', 'Demo exportação')
@section('icone-tela', 'fa-file-text-o')
@section('titulo-tela', 'Demonstração TR 4.2 / 4.3')
@section('lead')
    Emite a amostra Laguna com cabeçalho institucional, assinaturas e QR de validação.
@endsection
@section('acoes-topo')
    @include('exportacao::partials.botoes-exportar', [
        'action' => url('/exportacao/demo/emitir'),
        'formatos' => $formatos ?? ['pdf', 'xlsx', 'csv', 'html'],
        'formatoPadrao' => ($padrao[0] ?? 'pdf'),
        'opcoes' => [
            ['value' => 'amostra', 'label' => 'Amostra Laguna (TR 4.2/4.3)'],
        ],
        'tituloModal' => 'Download — Demo exportação',
        'leadModal' => 'PDF no modelo de declaração (borda, cabeçalho, logos, rodapé e QR).',
    ])
    <a class="botao claro" href="{{ url('/exportacao') }}"><i class="fa fa-arrow-left"></i> Painel</a>
@endsection

@section('conteudo')
    <div class="bloco">
        <h2><i class="fa fa-info-circle"></i> Como funciona</h2>
        <p style="margin:0;color:var(--suave)">
            Use o botão <strong>Download</strong> no topo para escolher o formato.
            O PDF reutiliza o modelo de declaração dos Relatórios Avançados (sem alterar aquele pacote).
        </p>
    </div>
@endsection
