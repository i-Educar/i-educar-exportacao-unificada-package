# Matriz PoC inicial — Onda 0.1

Classificação **por módulo** (visão executiva) + detalhe do módulo 4.  
Detalhamento item a item dos demais módulos continua em cada `REQUISITOS-TR.md` nas ondas seguintes.

Legenda: **atende** · **parcial** · **gap** · **adaptar** (não pacote Buriti novo)

| Módulo | Pasta | Classificação Onda 0 | Risco PoC | Próxima onda |
| --- | --- | --- | --- | ---: |
| **4** Requisitos mínimos | `i-educar-exportacao-unificada-package/` | **parcial** — 4.2/4.3 atende (export); 4.1 atende núcleo; 4.4–4.8 parcial/gap | médio | 0→1 |
| **5.1** Servidor | `i-educar-servidor-educacional-package/` | **gap** (scaffold) | alto | 2 |
| **5.2** Alimentação | `i-educar-alimentacao-escolar-package/` | **gap** (scaffold) | alto | 2 |
| **5.3** Escolar | `i-educar-escolar-aderencia-package/` + núcleo | **parcial** (núcleo forte) | baixo–médio | 1 |
| **5.4** Transporte | `i-educar-transporte-escolar-v2/` | **parcial** (v2 avançada) | médio | 1 |
| **5.5** Biblioteca | `i-educar-biblioteca-package/` + núcleo | **parcial** | médio | 2–3 |
| **5.6** Solicitação serviços | `i-educar-solicitacao-servicos-package/` | **gap** | médio | 2–3 |
| **5.7** Almoxarifado | `i-educar-almoxarifado-escolar-package/` | **gap** | médio | 2–3 |
| **5.8** Fila / pré-matrícula | Portabilis `pre-matricula-digital` | **adaptar** | médio | 1 |
| **5.9** Atendimento | `i-educar-atendimento-estudante-package/` | **gap** | médio | 2 |
| **5.10** Censo | `i-educar-educacenso-import-package/` | **parcial** | médio | 1 |
| **5.11** Horário | `i-educar-horario-automatico-package/` | **gap** | alto | 2–3 |
| **5.12** Eventos / formação | `i-educar-eventos-formacao-package/` | **gap** | médio | 2–3 |
| **5.13** App pais | `i-educar-apps-api-package/` | **gap** (API única) | alto | 3 |
| **5.14** App professores | `i-educar-apps-api-package/` | **gap** (API única) | alto | 3 |
| **5.15** Contratações | `i-educar-contratacoes-temporarias-package/` | **gap** | alto | 2 |

## Módulo 4 — item a item (fechado na Onda 0)

Ver `MATRIZ-POC-MODULO-4.md`.

| ID | Status |
| --- | --- |
| 4.1 | atende (núcleo web) |
| 4.2 | atende (export package — 9 formatos) |
| 4.3 | atende (export package — 6 formatos + JPEG) |
| 4.4 | parcial (núcleo) |
| 4.5 | parcial (núcleo) |
| 4.6 | gap |
| 4.7 | parcial |
| 4.8 | parcial (OCR 4.8–4.10) |

**Estimativa módulo 4 para PoC:** 5/8 atende ou demonstrável (~62% estrito; com parcial núcleo sobe para ~87% se 4.4/4.5/4.7/4.8 forem ensaiados no login/sessão). Gap duro: **4.6 correio interno**.

## Artefatos

- Status onda: `STATUS.md`
- Check ambiente: `php docs/onda-0/check-ambiente.php` (a partir da raiz do app)
- Matriz mestre OCR: `packages/buriti/docs/laguna/MATRIZ-REQUISITOS-TR.md`
