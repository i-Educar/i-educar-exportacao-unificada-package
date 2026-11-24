# Exportação unificada (TR 4.x · Laguna)

Pacote Buriti para o i-Educar **2.12**. Centraliza a geração de listas/relatórios nos formatos exigidos pelo Anexo I (itens **4.2** e **4.3**): PDF, ODT, DOCX, ODS, XLSX, HTML, XML, CSV, TXT e JPEG.

## Onda 0

Gate: **um relatório em ≥ 3 formatos**. Este pacote gera **todos** os 10 formatos do TR.

```bash
# testes sem banco
php packages/buriti/i-educar-exportacao-unificada-package/tests/run.php

# demo no app (após composer plug-and-play / discover)
php artisan exportacao:demo
# arquivos em storage/app/exportacao-onda0/
```

## Uso no código

```php
use iEducar\Packages\BuritiExportacaoUnificada\Services\Exportador;
use iEducar\Packages\BuritiExportacaoUnificada\Support\Formato;
use iEducar\Packages\BuritiExportacaoUnificada\Support\TabelaExportacao;

$exportador = app(Exportador::class);
$tabela = new TabelaExportacao('Alunos', ['Nome', 'Turma'], [['Ana', '1A']]);
$arquivo = $exportador->exportar($tabela, Formato::Csv);
```

## Documentos Laguna

- `EPIC.md` · `ROADMAP.md` · `TODO.md` · `REQUISITOS-TR.md`
- `docs/onda-0/STATUS.md` — evidência da Onda 0
- `docs/onda-0/MATRIZ-POC-MODULO-4.md` · `MATRIZ-POC-INICIAL.md`
- `docs/onda-0/check-ambiente.php` — Onda 0.2

```bash
php packages/buriti/i-educar-exportacao-unificada-package/docs/onda-0/check-ambiente.php
```
