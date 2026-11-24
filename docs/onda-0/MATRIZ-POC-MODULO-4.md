# Matriz PoC — Módulo 4 (Onda 0)

Classificação inicial dos 8 itens OCR do TR 4.x após a entrega do pacote de exportação.

Legenda: **atende** · **parcial** · **gap** · **N/A (OCR)**

| ID | Resumo | Status | Evidência / nota |
| --- | --- | --- | --- |
| 4.1 | Full-web (navegador, sem cliente pesado) | **atende** | i-Educar 2.12 Laravel web; apps móveis à parte (5.13/5.14) |
| 4.2 | Relatórios PDF ISO 32000 + ODT/DOCX/ODS/XLSX/HTML/XML/CSV/TXT | **parcial → atende (export)** | `Exportador` gera os 9 formatos; PDF mínimo ISO 32000; ainda falta ligar a *todos* os relatórios do sistema |
| 4.3 | Listas/processos PDF/TXT/CSV/XLSX/DOCX/JPEG | **parcial → atende (export)** | Mesmo serviço; JPEG via GD; adoção nas telas de lista = Onda 1+ |
| 4.4 | Multi-instância sem bloquear login duplicado | **parcial** | Revisar política de sessão do núcleo; fora do pacote de export |
| 4.5 | Base única / unicidade de pessoa / consolidação | **parcial** | Núcleo já unifica pessoa; consolidação avançada = gap fino |
| 4.6 | Correio interno + leitura obrigatória | **gap** | Não coberto nesta onda |
| 4.7 | Bloqueio acesso / troca senha cíclica | **parcial** | Núcleo tem usuários/senha; validar item a item na PoC |
| 4.8 | Usuário↔pessoa, recuperação senha, ver senha, troca unidade, timeout | **parcial** | Mistura OCR 4.8–4.10; validar no núcleo + Gov.br se houver |

## Cobertura Onda 0 (escopo desta onda)

- Itens **4.2 e 4.3** (motores de formato): **atendem** via `buriti/i-educar-exportacao-unificada-package`.
- Demais itens 4.x: classificados; implementação além de export fica para ondas seguintes / núcleo.

## Próximo passo documental

Replicar este modelo (`atende/parcial/gap`) nos demais `REQUISITOS-TR.md` durante a Onda 1 (começar por 5.3, 5.8, 5.4, 5.10).
