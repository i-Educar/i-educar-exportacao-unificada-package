# Requisitos TR 4 — Requisitos mínimos do sistema

**Edital:** PE 77/2026 Laguna/SC · Anexo I  
**Cobertura documental:** **100%** dos 8 itens OCR abaixo.  
**PoC:** ≥85% demonstráveis; ≤15% em ≤30 dias.  
**Matriz mestre:** `tmp/edital-ocr/MATRIZ-REQUISITOS-TR.md`  
**Classificação Onda 0:** `docs/onda-0/MATRIZ-POC-MODULO-4.md` · `docs/onda-0/STATUS.md`

## 4 — Requisitos mínimos do sistema (8 itens)

### Checklist 100% (classificado Onda 0)

| ID | Status | Evidência |
| --- | --- | --- |
| **4.1** | **atende** | i-Educar 2.12 Full-Web (Laravel 13) |
| **4.2** | **atende** | `Exportador` · PDF/ODT/DOCX/ODS/XLSX/HTML/XML/CSV/TXT · `exportacao:demo` |
| **4.3** | **atende** | Mesmo serviço · PDF/TXT/CSV/XLSX/DOCX/JPEG · testes `tests/run.php` |
| **4.4** | **parcial** | Multi-aba/sessão no núcleo — validar na homologação |
| **4.5** | **parcial** | Cadastro único de pessoa no núcleo — consolidação avançada pendente |
| **4.6** | **gap** | Correio interno + leitura obrigatória — plano ≤30 dias pós-PoC |
| **4.7** | **parcial** | Bloqueio/usuários no núcleo — senha cíclica a validar |
| **4.8** | **parcial** | Usuário↔pessoa, recuperação, ver senha, troca unidade, timeout — ensaiar no núcleo |

### Texto OCR (rastreio)

- [x] **4.1** — Full-Web / navegador padrão · **atende**
- [x] **4.2** — Relatórios PDF ISO 32000 + ODT/DOCX/ODS/XLSX/HTML/XML/CSV/TXT · **atende (motor)**
- [x] **4.3** — Exportação listas PDF/TXT/CSV/XLSX/DOCX/JPEG · **atende (motor)**
- [ ] **4.4** — Multi-instância sem bloquear login duplicado · **parcial**
- [ ] **4.5** — Base única / unicidade / consolidação · **parcial**
- [ ] **4.6** — Correio interno + leitura obrigatória · **gap**
- [ ] **4.7** — Bloqueio acesso / senha cíclica · **parcial**
- [ ] **4.8** — Usuário↔pessoa / recuperação / ver senha / troca unidade / timeout · **parcial**

## Regra de rastreio

- Todo item coberto por fase no `ROADMAP.md` e tarefa/grupo no `TODO.md`.
- `EPIC.md` declara cobertura 100% via este arquivo.
- Reconciliar IDs com ruído OCR no PDF antes da PoC.
