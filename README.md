# Exportação unificada

Motor compartilhado de emissão de listas e relatórios em múltiplos formatos, com layout institucional e validação por QR.

Composer: `buriti/i-educar-exportacao-unificada-package`

## Para que serve

Padroniza a saída documental dos módulos Buriti (PDF, ODT, DOCX, ODS, XLSX, HTML, XML, CSV, TXT, JPEG) com cabeçalho/rodapé/logos/assinaturas. **Não** substitui o pacote de Relatórios avançados.

## Desenho de funções

| Função | Entrega |
| --- | --- |
| Drivers multi-formato | Dez formatos a partir do mesmo conteúdo |
| Layout institucional | Identidade, assinaturas e rodapé padronizados |
| Validador / QR | Link público de autenticação do documento |
| Painel de configuração | `/exportacao` — identidade e formatos |
| Demo de emissão | `/exportacao/demo` e comando `exportacao:demo` |
| API de serviço | Consumo pelos demais pacotes (`EmissorDocumento`) |

## Quem usa

- Todos os módulos emissores
- SME (configuração visual)
- Público (validação de documento)

## Integrações

- Relatórios avançados (validador, quando disponível)
- Pacotes Buriti consumidores

## Compatibilidade

| Produto | Versão |
| --- | --- |
| **i-Educar** | **2.12.x** |
| **i-Diário** | Portabilis i-Diário (API / SSO do núcleo) |
| Pacote | `0.3.2` |

Declarado em `composer.json` → `extra.ieducar` / `extra.idiario`.

## Documentação

| Documento | Uso |
| --- | --- |
| [`EPIC.md`](./EPIC.md) | Escopo de produto, aceite e critérios |
| [`ROADMAP.md`](./ROADMAP.md) | Fases e entregas |
| [`TODO.md`](./TODO.md) | Backlog operacional |
| [`REQUISITOS-TR.md`](./REQUISITOS-TR.md) | Rastreio de requisitos (planejamento) |

---

Licença: **GPL-2.0-or-later** · Organização [i-Educar](https://github.com/i-Educar)
