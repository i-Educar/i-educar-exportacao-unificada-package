# TODO — Exportação Unificada (requisitos mínimos)

> Pacote: `buriti/i-educar-exportacao-unificada-package` · TR **4.x** · Prioridade **P1**

## Onda 0 (concluída no código)

- [x] Matriz 4.x classificada (`docs/onda-0/`)
- [x] Pacote serviço `Exportador` + drivers
- [x] Drivers PDF/ODT/DOCX/ODS/XLSX/HTML/XML/CSV/TXT/JPEG
- [x] Testes standalone (`tests/run.php` — 60 ok)
- [x] Artisan `exportacao:demo` (10 formatos)
- [x] Scaffold 2.12 (`composer.json`, provider, plug-and-play path)
- [x] README + docs onda 0

## Backlog Laguna (próximo)

- [ ] Integrar relatórios/listas 5.3 / 5.4 / 5.8 ao `Exportador`
- [ ] Checklist multi-instância (4.4) na homologação
- [ ] Correio interno (4.6) — gap ≤30 dias
- [ ] Validar 4.5 / 4.7 / 4.8 no núcleo com roteiro PoC
- [ ] Testes golden files (conteúdo binário estável)
- [ ] Completar Onda 0.2 quando Postgres/homolog subir (`docs/onda-0/check-ambiente.php`)

## Cobertura 100% do TR

- [x] Percorrer 100% de [`REQUISITOS-TR.md`](./REQUISITOS-TR.md) e marcar atende/gap/parcial
- [x] Abrir tarefas para gaps (4.6 + parcials acima)
- [x] Evidências de PoC para 4.2/4.3 (`storage/app/exportacao-onda0/`)
- [ ] Lista formal dos ≤15% com datas ≤30 dias (após homolog)

## Gate PoC

- [x] `REQUISITOS-TR.md` 100% classificado
- [ ] ≥ 85% atende com evidência (depende ensaio 4.4/4.5/4.7/4.8 + gap 4.6)
- [ ] ≤ 15% gap com plano ≤ 30 dias
- [ ] Roteiro binário ensaiado

## Referências

- `packages/buriti/ORDEM-DESENVOLVIMENTO-LAGUNA.md`
- `packages/buriti/docs/laguna/DOC-EXECUTIVO-PE-77-2026-LAGUNA-IEDUCAR.md`
- OCR: `packages/buriti/docs/laguna/MATRIZ-REQUISITOS-TR.md`
