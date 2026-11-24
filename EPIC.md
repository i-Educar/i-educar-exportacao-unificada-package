# EPIC — Exportação Unificada (requisitos mínimos)

| Campo | Valor |
| --- | --- |
| Pacote | `buriti/i-educar-exportacao-unificada-package` |
| TR Laguna | **4.x** |
| Prioridade | **P1** |
| Nota atual (executivo) | **B** |
| Fonte | `tmp/DOC-EXECUTIVO-PE-77-2026-LAGUNA-IEDUCAR.md` |
| Regra de ouro | Pregão Eletrônico 77/2026 · Laguna/SC · PoC ≥ 85% por módulo · gaps ≤ 30 dias |
| Base atual | **Onda 0:** drivers unificados PDF/ODT/DOCX/ODS/XLSX/HTML/XML/CSV/TXT/JPEG operacionais (`exportacao:demo`) |

## Objetivo

Serviço único de exportação cobrindo PDF ISO 32000, ODT, DOCX, ODS, XLSX, HTML, XML, CSV, TXT e JPEG.

## Padrão técnico (i-Educar 2.12)

| Item | Valor |
| --- | --- |
| Alvo | i-Educar **2.12** · PHP **≥ 8.3** · Illuminate **^11|^12|^13** (Laravel 13 no app) |
| Composer | `buriti/i-educar-exportacao-unificada-package` |
| Namespace | `iEducar\Packages\BuritiExportacaoUnificada\` → `src/` |
| Compat | `extra.ieducar.compatible: ["2.12"]` · GPL-2.0-or-later · `type: library` |
| Schema / dados | nenhum (serviço) — opcional cache em storage |
| Rotas | prefixo `exportacao (serviço interno / rotas admin)`; SME com middleware `ieducar.*` + `auth`; API apps via Sanctum quando exposto |
| Tenancy | `PackageTenant::allows('buriti/i-educar-exportacao-unificada-package')` antes de migrations/menu |
| Plug-and-play | path em `packages/buriti/…`; manter em `ignore` até instalável |
| Referência | `packages/buriti/PADRAO-PACOTE-2.12.md` |

**Nota de arquitetura:** Drivers de formato; reutilizar Maatwebsite/PhpSpreadsheet/PDF já no app 2.12 quando possível.

## Cobertura 100% do TR

Este EPIC cobre **todos** os requisitos do módulo(s) 4 listados em [`REQUISITOS-TR.md`](./REQUISITOS-TR.md) (8 itens OCR).

- PoC: ≥ **85%** demonstráveis e operacionais.
- Até **15%** restante: produção em ≤ **30 dias**, sem ônus.
- Nenhum item do `REQUISITOS-TR.md` pode ficar fora do `ROADMAP.md` / `TODO.md` (direto ou por grupo rastreável).

## Épicos filhos

1. API de exportação com drivers por formato.
2. Catálogo de relatórios elegíveis e testes de conformidade.
3. Revisão multi-instância / unicidade de pessoa / troca de unidade.

## Critérios de aceite (PoC)

- [x] Checklist [`REQUISITOS-TR.md`](./REQUISITOS-TR.md) com **100%** dos itens classificados (atende / parcial / gap)
- [x] Motor 4.2/4.3 demonstrável (10 formatos + testes)
- [ ] Itens **atende** demonstráveis na PoC (≥ 85% do módulo 4) — depende ensaio 4.4–4.8 e gap 4.6
- [ ] Itens **gap** ≤ 15% com plano ≤ 30 dias em produção, sem ônus
- [x] Nenhum requisito do TR omitido da matriz


## Fora de escopo deste EPIC

- Orçamento comercial da proposta Laguna.
- Substituição do core i-Educar fora do domínio do módulo.
