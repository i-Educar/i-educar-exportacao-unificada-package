# Exportação unificada (TR 4.x · Laguna)

Pacote Buriti para o i-Educar **2.12**. Centraliza a geração de listas/relatórios nos formatos exigidos pelo Anexo I (itens **4.2** e **4.3**): PDF, ODT, DOCX, ODS, XLSX, HTML, XML, CSV, TXT e JPEG.

**Não substitui** o pacote Serventec de Relatórios avançados — é o motor compartilhado dos demais módulos Buriti, com layout institucional e QR/link do validador de documentos.

## Compatibilidade

| Produto | Versão |
| --- | --- |
| **i-Educar** | **2.12.x** (min. 2.12.0) |
| **i-Diário** | Portabilis i-Diário (espelho Laguna) via API/SSO do core |
| Pacote | `0.3.0` |

Declarado em `composer.json` → `extra.ieducar` e `extra.idiario`.

## UI / Laguna PoC

| Rota | Função |
| --- | --- |
| `/exportacao` | Painel de configuração (identidade, formatos, validador) |
| `/exportacao/demo` | Emite amostra TR com layout + QR |
| `/exportacao/validar/{code}` | Validação pública (QR) |

Catálogo: `CatalogoModulos` → `id: exportacao` · `admin_url: /exportacao` · comandos `exportacao:demo`.

## Onda 0

Gate: **um relatório em ≥ 3 formatos**. Este pacote gera **todos** os 10 formatos do TR.

```bash
# testes sem banco
php packages/buriti/i-educar-exportacao-unificada-package/tests/run.php

# demo tabular
php artisan exportacao:demo

# demo com cabeçalho/rodapé/assinaturas/QR
php artisan exportacao:demo --institucional
```

## Uso nos módulos (preferir)

```php
use iEducar\Packages\BuritiExportacaoUnificada\Services\EmissorDocumento;
use iEducar\Packages\BuritiExportacaoUnificada\Support\Formato;
use iEducar\Packages\BuritiExportacaoUnificada\Support\TabelaExportacao;

return app(EmissorDocumento::class)->download(
    new TabelaExportacao('Alunos', ['Nome', 'Turma'], [['Ana', '1A']]),
    Formato::Pdf,
    'Escolar',
    auth()->user()?->name,
);
```

Motor tabular puro (sem layout/QR):

```php
use iEducar\Packages\BuritiExportacaoUnificada\Services\Exportador;
use iEducar\Packages\BuritiExportacaoUnificada\Support\Formato;
use iEducar\Packages\BuritiExportacaoUnificada\Support\TabelaExportacao;

$arquivo = app(Exportador::class)->exportar(
    new TabelaExportacao('Alunos', ['Nome', 'Turma'], [['Ana', '1A']]),
    Formato::Csv,
);
```

## Documentos Laguna

- `EPIC.md` · `ROADMAP.md` · `TODO.md` · `REQUISITOS-TR.md`
- `docs/onda-0/STATUS.md` — evidência da Onda 0
- `docs/onda-0/MATRIZ-POC-MODULO-4.md` · `MATRIZ-POC-INICIAL.md`
- `docs/onda-0/check-ambiente.php` — Onda 0.2

```bash
php packages/buriti/i-educar-exportacao-unificada-package/docs/onda-0/check-ambiente.php
```
