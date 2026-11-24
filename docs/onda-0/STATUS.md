# Status — Onda 0 (Laguna PE-77/2026)

Data: 2026-11-24  
Pacote: `buriti/i-educar-exportacao-unificada-package` 0.1.0

## Gate da onda

| Critério | Resultado |
| --- | --- |
| Exportação mínima operacional (≥ 3 formatos) | **OK — 10/10 formatos TR** |
| Testes automatizados sem banco | **OK — 60 asserts, 0 falhas** |
| Comando Artisan de prova | **OK — `php artisan exportacao:demo`** |
| Ambiente app 2.12 (PHP/Laravel) | **OK — PHP 8.4.24 · Laravel 13.31.0** |
| Rede mínima no banco (escolas/alunos) | **PENDENTE — Postgres local connection refused** |

## Evidências 0.3 — exportação

Comando:

```bash
php packages/buriti/i-educar-exportacao-unificada-package/tests/run.php
php artisan exportacao:demo
```

Arquivos gerados em `storage/app/exportacao-onda0/`:

| Arquivo | Formato TR |
| --- | --- |
| `prova-onda0-laguna.pdf` | 4.2 / 4.3 |
| `prova-onda0-laguna.odt` | 4.2 |
| `prova-onda0-laguna.docx` | 4.2 / 4.3 |
| `prova-onda0-laguna.ods` | 4.2 |
| `prova-onda0-laguna.xlsx` | 4.2 / 4.3 |
| `prova-onda0-laguna.html` | 4.2 |
| `prova-onda0-laguna.xml` | 4.2 |
| `prova-onda0-laguna.csv` | 4.2 / 4.3 |
| `prova-onda0-laguna.txt` | 4.2 / 4.3 |
| `prova-onda0-laguna.jpg` | 4.3 |

## Evidências 0.1 — matriz módulo 4

Ver `MATRIZ-POC-MODULO-4.md` neste diretório (classificação atende/gap/N/A).

## Evidências 0.2 — homologação

Script: `php packages/buriti/i-educar-exportacao-unificada-package/docs/onda-0/check-ambiente.php`

| Check | Status |
| --- | --- |
| PHP 8.4.24 + ext pgsql/gd/zip/xml | OK |
| `.env` / `APP_KEY` / `DB_*` | OK |
| `php artisan about` | App local, tenant1, locale pt-br |
| Postgres `localhost:5432` | **FAIL** — connection refused (Docker daemon off) |
| Redis `localhost:6379` | WARN — não alcançável |
| Artefatos exportação | OK 10/10 |
| Seeds / `ieducar:matriculas` | Bloqueado até subir o banco |

### Como completar 0.2 quando o banco estiver no ar

```bash
# subir PostgreSQL (docker/serviço local)
php artisan db:show
php artisan migrate --force
# se perfil setup disponível:
# php artisan ieducar:matriculas
php artisan about
```

Critério: login SME + pelo menos 1 escola, 1 turma e alunos listáveis.

## Integração no monorepo

- Composer path + require em `composer.json` / `packages/plug-and-play.json`
- Symlink `vendor/buriti/i-educar-exportacao-unificada-package`
- Provider em `config/app.php` e `bootstrap/cache/packages.php`
- Autoload PSR-4 `iEducar\Packages\BuritiExportacaoUnificada\`

> Nota: `composer plug-and-play` completo falhou por conflito de `roave/security-advisories` com PHP 8.4.24 (assunto à parte). O pacote está operacional via path/autoload/provider.
