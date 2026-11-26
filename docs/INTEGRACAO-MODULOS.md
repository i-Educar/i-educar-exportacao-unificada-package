# Integração — Exportação unificada nos módulos

**Regra:** todo módulo Buriti (exceto **Relatórios avançados** / Serventec) envia dados tabulares; o pacote `exportacao-unificada` gera PDF/XLSX/CSV/… com layout institucional e QR.

## Contrato

```php
use iEducar\Packages\BuritiExportacaoUnificada\Support\ExportacaoModulo;

return ExportacaoModulo::download(
    titulo: 'Lista de servidores',
    cabecalhos: ['ID', 'Nome', 'Escola'],
    linhas: [[1, 'Ana', 'EMEF Centro'], ...],
    moduloOrigem: 'RH educacional',
    formato: $request->input('formato', 'pdf'),
    subtitulo: 'Escopo da rede',
    nomeBase: 'rh-servidores',
);
```

## UI — botão Download no topo + modal

Coloque no **começo** da tela (`@section('acoes-topo')`):

```blade
@section('acoes-topo')
    @include('exportacao::partials.botoes-exportar', [
        'action' => url('/meu-modulo/exportar'),
        'formatos' => ['pdf', 'xlsx', 'csv', 'html'],
        'opcoes' => [
            ['value' => 'lista', 'label' => 'Lista completa'],
            ['value' => 'resumo', 'label' => 'Resumo'],
        ],
        'campoExtra' => ['q' => $busca ?? ''],
        'tituloModal' => 'Download — Meu módulo',
    ])
@endsection
```

O modal pede **o que** exportar (`item`) e o **formato**. PDF usa o modelo de declaração dos Relatórios Avançados (borda, cabeçalho, logos, rodapé, QR) via `PdfRenderService` — **sem alterar** o pacote Serventec.

## O que o módulo de origem NÃO faz

- Não monta PDF/HTML institucional próprio
- Não implementa drivers CSV/XLSX/DOCX
- Não substitui Relatórios avançados (consultas complexas Serventec)
- Não edita views/classes de `i-educar-advanced-reports-package`

## O que a exportação faz

- Modal de escolha (conteúdo + formato + QR)
- PDF no modelo de declaração (reuso de `advanced-reports::pdf.*`)
- Layout HTML (logos, cabeçalho, assinaturas, rodapé)
- Formatos TR 4.2 / 4.3
- Validação/QR (`ValidadorDocumentoBridge`)
