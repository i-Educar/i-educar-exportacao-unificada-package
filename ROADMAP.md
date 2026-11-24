# ROADMAP — Exportação Unificada (requisitos mínimos)

> Pacote: `buriti/i-educar-exportacao-unificada-package` · TR **4.x** · Prioridade **P1** · Nota **B**  
> Alinhado a `tmp/DOC-EXECUTIVO-PE-77-2026-LAGUNA-IEDUCAR.md`

## Visão

Serviço único de exportação cobrindo PDF ISO 32000, ODT, DOCX, ODS, XLSX, HTML, XML, CSV, TXT e JPEG.

## Fases

### F0 · Matriz formatos — ✅ Onda 0

Onde cada formato é exigido · classificado em `docs/onda-0/`.

### F1 · Core export — ✅ Onda 0

Drivers PDF/ODT/DOCX/ODS/XLSX/CSV/HTML/XML/TXT/JPEG · `tests/run.php` · `exportacao:demo`.

### F2 · Adoção

Ligar listas/relatórios críticos da PoC.

### F3 · Sessão/instância

Revisar requisitos 4.x não-export (4.4–4.8).

### F4 · PoC

Bateria de downloads na demo.

## Cobertura 100% dos requisitos (TR)

Fonte: [`REQUISITOS-TR.md`](./REQUISITOS-TR.md) — **8 itens**.

Cada lote deve sair da PoC como **atende** ou entrar no plano de 30 dias.

### Lote R1 — itens 4.1 … 4.8 (8)

- `4.1` atende · `4.2` atende · `4.3` atende · `4.4` parcial · `4.5` parcial · `4.6` gap · `4.7` parcial · `4.8` parcial

- [x] Classificar / evidenciar motor 4.2–4.3 na matriz PoC (`docs/onda-0/`)
- [ ] Validar parcials 4.4/4.5/4.7/4.8 na homologação
- [ ] Implementar gap 4.6 (correio) ≤30 dias

### Gate 100%

- [x] Lote R1 classificado (atende / parcial / gap)
- [ ] Soma atende ≥ 85% na demonstração (ensaio homolog)

## Conformidade Laravel / pacote i-Educar 2.12

Onda 0 entregue:

1. [x] `composer.json` (`php >=8.3`, illuminate, `extra.laravel`, `compatible: ["2.12"]`)
2. [x] Scaffold: `src/Providers`, `Services`, `Drivers`, `Support`, `Console`
3. [x] Sem schema próprio (serviço puro)
4. [ ] Menu/processos (quando integrar UI)
5. [x] Sem UI mobile; apps via `buriti/i-educar-apps-api-package`
6. [x] Seguir `packages/buriti/PADRAO-PACOTE-2.12.md`

## Dependências

- Cadastro mestre de aluno/escola/turma no i-Educar (SGE).
- Matriz item a item OCR do Anexo I antes de cravar %.
- Alinhamento com `i-educar-apps-api-package` (APIs 5.13/5.14; UI/lojas em equipe externa) e exports (4.x) quando o módulo expõe dados ao cidadão/SME.

## Riscos

- Valor global do edital incompatível com greenfield total — reuso e MVP demonstrável são obrigatórios.
- PDF escaneado: numerações OCR podem exigir reconciliação manual.
- PoC só conta o que estiver operacional no dia.

## Definição de pronto da fase PoC

Checklist do `TODO.md` marcado para a fatia MVP + roteiro de demonstração ensaiado.
