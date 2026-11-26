# Status — integração dos módulos → exportação unificada

**Regra:** módulo de origem monta `cabecalhos` + `linhas`; `ExportacaoModulo` / `EmissorDocumento` gera arquivos (layout + formatos TR). **Exceto** Relatórios avançados (Serventec).

| Módulo | Endpoint | Status |
| --- | --- | --- |
| Almoxarifado | `POST …/movimentos/exportar` | OK |
| Solicitação | `POST …/relatorios/exportar` | OK |
| RH | `POST …/servidores\|lotacoes/exportar` | OK |
| Alimentação | `POST …/fichas-tecnicas/exportar` | OK |
| Horário | `POST …/relatorio/exportar` | OK |
| Busca ativa | `POST …/relatorios/exportar` | OK |
| Biblioteca | `POST …/emprestimos/exportar` | OK |
| Eventos | `POST …/eventos/exportar` | OK |
| Contratações | `POST …/classificados/exportar` | OK |
| Atendimento | `POST …/historico/exportar` | OK |
| Transporte | PDF/CSV locais (`PdfSimples`, SETE) | pendente (híbrido) |
| Relatórios avançados | — | **fora de escopo** |

API: `docs/INTEGRACAO-MODULOS.md` · helper `Support/ExportacaoModulo.php`.
