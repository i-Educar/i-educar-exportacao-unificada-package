<?php

namespace iEducar\Packages\BuritiExportacaoUnificada\Services;

use iEducar\Packages\BuritiExportacaoUnificada\Support\DocumentoInstitucional;
use iEducar\Packages\BuritiExportacaoUnificada\Support\TabelaExportacao;

/**
 * HTML padronizado: cabeçalho institucional, tabela, assinaturas, QR/validação, rodapé.
 */
final class LayoutInstitucional
{
    public function html(TabelaExportacao $tabela, ?DocumentoInstitucional $doc = null): string
    {
        $doc ??= DocumentoInstitucional::fromConfig($tabela->titulo, $tabela->subtitulo);
        $e = static fn (string $v): string => htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $logoE = $doc->logoEsquerda !== ''
            ? '<img class="logo" src="'.$e($doc->logoEsquerda).'" alt="Logo">'
            : '<div class="logo-placeholder">'.mb_substr($doc->entidade, 0, 1).'</div>';
        $logoD = $doc->logoDireita !== ''
            ? '<img class="logo" src="'.$e($doc->logoDireita).'" alt="Brasão">'
            : '';

        $assinaturasHtml = '';
        if ($doc->assinaturas !== []) {
            if (class_exists(\iEducar\Packages\BuritiAssinaturaDigital\Support\BlocoAssinatura::class)) {
                $assinaturasHtml = \iEducar\Packages\BuritiAssinaturaDigital\Support\BlocoAssinatura::gradeHtml(
                    $doc->assinaturas,
                    [
                        'cor' => (string) config('exportacao.institucional.cor', '#0a7a8a'),
                    ],
                );
            } else {
                $assinaturasHtml .= '<section class="assinaturas"><h2>Assinaturas</h2><div class="grade-assina">';
                foreach ($doc->assinaturas as $a) {
                    $nome = trim((string) ($a['nome'] ?? ''));
                    $cargo = trim((string) ($a['cargo'] ?? 'Responsável'));
                    $quando = trim((string) ($a['quando'] ?? ''));
                    $codigo = trim((string) ($a['codigo_validacao'] ?? ''));
                    $url = trim((string) ($a['url_validacao'] ?? ''));
                    $auth = '';
                    if ($codigo !== '' || $url !== '') {
                        $label = 'Conferir autenticidade'.($codigo !== '' ? ' · '.$codigo : '');
                        $auth = $url !== ''
                            ? '<a class="ls-auth" href="'.$e($url).'">'.$e($label).'</a>'
                            : '<span class="ls-auth">'.$e($label).'</span>';
                    }
                    $assinaturasHtml .= '<div class="assina">'
                        .'<div class="linha-assina"></div>'
                        .'<strong>'.$e($nome !== '' ? $nome : '________________').'</strong>'
                        .'<span>'.$e($cargo).'</span>'
                        .($quando !== '' ? '<small>'.$e($quando).'</small>' : '')
                        .$auth
                        .'</div>';
                }
                $assinaturasHtml .= '</div></section>';
            }
        }

        $validacaoHtml = '';
        if ($doc->comValidacao && $doc->urlValidacao) {
            $qr = $doc->qrDataUri
                ? '<img class="qr" src="'.$e($doc->qrDataUri).'" alt="QR de validação" width="120" height="120">'
                : '';
            $lsHint = class_exists(\iEducar\Packages\BuritiAssinaturaDigital\Services\AssinaturaOrquestrador::class)
                ? '<p class="hint">Assinatura digital (LibreSign): confira também os códigos sob cada assinatura, quando houver.</p>'
                : '';
            $validacaoHtml = '<section class="validacao">'
                .$qr
                .'<div><strong>Autenticidade do documento</strong>'
                .'<p>Código: <code>'.$e((string) $doc->codigoValidacao).'</code></p>'
                .'<p class="url">'.$e((string) $doc->urlValidacao).'</p>'
                .'<p class="hint">Escaneie o QR ou abra o link — verde na tela = documento válido.</p>'
                .$lsHint
                .'</div></section>';
        }

        $rows = '';
        foreach ($tabela->matriz() as $linha) {
            $rows .= '<tr>';
            foreach ($linha as $cel) {
                $rows .= '<td>'.$e($cel).'</td>';
            }
            $rows .= '</tr>';
        }

        $ths = '';
        foreach ($tabela->cabecalhos as $c) {
            $ths .= '<th>'.$e($c).'</th>';
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>{$e($doc->titulo)}</title>
<style>
  :root { --cor:#0a7a8a; --linha:#cfd8df; --suave:#5b6b76; --tinta:#1a242b; }
  * { box-sizing:border-box; }
  body { margin:0; font-family:"Segoe UI",Arial,sans-serif; color:var(--tinta); font-size:13px; }
  .folha { max-width:900px; margin:0 auto; padding:28px 32px 40px; }
  .cabeca { display:flex; gap:16px; align-items:center; border-bottom:3px solid var(--cor); padding-bottom:14px; margin-bottom:18px; }
  .cabeca .logos { display:flex; gap:12px; align-items:center; }
  .logo { max-height:64px; max-width:120px; object-fit:contain; }
  .logo-placeholder { width:56px; height:56px; border-radius:12px; background:linear-gradient(145deg,#1495a8,#0a7a8a); color:#fff; display:grid; place-items:center; font-size:1.4rem; font-weight:800; }
  .cabeca h1 { margin:0 0 4px; font-size:1.05rem; color:var(--cor); text-transform:uppercase; letter-spacing:.04em; }
  .cabeca .entidade { margin:0; font-size:1.15rem; font-weight:800; }
  .cabeca .meta { margin:4px 0 0; color:var(--suave); font-size:.82rem; }
  .titulo-doc { margin:0 0 6px; font-size:1.35rem; }
  .sub { margin:0 0 16px; color:var(--suave); }
  table { width:100%; border-collapse:collapse; margin:0 0 22px; }
  th, td { border:1px solid var(--linha); padding:7px 9px; text-align:left; }
  th { background:#e6f5f7; color:var(--cor); font-size:.78rem; text-transform:uppercase; letter-spacing:.03em; }
  .assinaturas { page-break-inside:avoid; margin:22px 0 8px; }
  .assinaturas h2, .validacao strong { color:var(--cor); font-size:.9rem; margin:0 0 6px; text-transform:uppercase; letter-spacing:.04em; }
  .grade-assina { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:32px 28px; margin-top:8px; }
  .assina { text-align:center; padding-top:44px; }
  .linha-assina { border-top:1px solid #1a242b; margin:0 18px 8px; height:0; }
  .assina strong { display:block; font-size:.88rem; color:var(--tinta); }
  .assina span, .assina small { display:block; color:var(--suave); font-size:.78rem; line-height:1.35; }
  .assina .ls-auth, .ls-assina .ls-auth { display:inline-block; margin-top:6px; font-size:.72rem; color:var(--cor); text-decoration:none; border-bottom:1px dotted var(--cor); }
  .ls-grade-assina { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:32px 28px; margin-top:8px; }
  .validacao { display:flex; gap:16px; align-items:center; margin:22px 0; padding:12px; border:1px dashed color-mix(in srgb,var(--cor) 40%,var(--linha)); border-radius:12px; background:#f7fbfc; }
  .validacao .qr { border:1px solid var(--linha); border-radius:8px; background:#fff; }
  .validacao .url { word-break:break-all; font-size:.75rem; color:var(--suave); }
  .validacao .hint { margin:4px 0 0; font-size:.75rem; color:var(--suave); }
  .rodape { margin-top:28px; padding-top:12px; border-top:1px solid var(--linha); color:var(--suave); font-size:.75rem; display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; }
  @media print { body { background:#fff; } .folha { padding:0; max-width:none; } }
</style>
</head>
<body>
<div class="folha">
  <header class="cabeca">
    <div class="logos">{$logoE}{$logoD}</div>
    <div>
      <h1>{$e($doc->secretaria)}</h1>
      <p class="entidade">{$e($doc->entidade)}</p>
      <p class="meta">{$e($doc->sistema)}
        {$e($doc->moduloOrigem !== '' ? ' · '.$doc->moduloOrigem : '')}
        {$e($doc->emitidoEm ? ' · Emitido em '.$doc->emitidoEm : '')}
        {$e($doc->emitidoPor ? ' · por '.$doc->emitidoPor : '')}
      </p>
    </div>
  </header>

  <h2 class="titulo-doc">{$e($doc->titulo)}</h2>
  <p class="sub">{$e($doc->subtitulo !== '' ? $doc->subtitulo : $tabela->subtitulo)}</p>

  <table>
    <thead><tr>{$ths}</tr></thead>
    <tbody>{$rows}</tbody>
  </table>

  {$assinaturasHtml}
  {$validacaoHtml}

  <footer class="rodape">
    <span>{$e($doc->rodapeExtra !== '' ? $doc->rodapeExtra : 'Documento gerado eletronicamente')}</span>
    <span>{$e($doc->sistema)} · exportação unificada</span>
  </footer>
</div>
</body>
</html>
HTML;
    }

    /** Linhas textuais para PDF mínimo / TXT. */
    public function linhasTexto(TabelaExportacao $tabela, ?DocumentoInstitucional $doc = null): array
    {
        $doc ??= DocumentoInstitucional::fromConfig($tabela->titulo, $tabela->subtitulo);
        $linhas = [
            $doc->entidade,
            $doc->secretaria,
            $doc->sistema.($doc->moduloOrigem !== '' ? ' · '.$doc->moduloOrigem : ''),
            str_repeat('=', 72),
            $doc->titulo,
        ];
        if ($doc->subtitulo !== '' || $tabela->subtitulo !== '') {
            $linhas[] = $doc->subtitulo !== '' ? $doc->subtitulo : $tabela->subtitulo;
        }
        if ($doc->emitidoEm) {
            $linhas[] = 'Emitido em '.$doc->emitidoEm.($doc->emitidoPor ? ' por '.$doc->emitidoPor : '');
        }
        $linhas[] = '';
        $linhas[] = implode(' | ', $tabela->cabecalhos);
        $linhas[] = str_repeat('-', 72);
        foreach ($tabela->matriz() as $linha) {
            $linhas[] = implode(' | ', $linha);
        }
        if ($doc->assinaturas !== []) {
            $linhas[] = '';
            $linhas[] = 'Assinaturas:';
            foreach ($doc->assinaturas as $a) {
                $linhas[] = '  - '.trim((string) ($a['cargo'] ?? 'Responsável'))
                    .(! empty($a['nome']) ? ': '.$a['nome'] : '');
                $cod = trim((string) ($a['codigo_validacao'] ?? ''));
                $urlA = trim((string) ($a['url_validacao'] ?? ''));
                if ($cod !== '' || $urlA !== '') {
                    $linhas[] = '    Conferir autenticidade'
                        .($cod !== '' ? ' · '.$cod : '')
                        .($urlA !== '' ? ' · '.$urlA : '');
                }
            }
        }
        if ($doc->urlValidacao) {
            $linhas[] = '';
            $linhas[] = 'Validação: '.($doc->codigoValidacao ?? '');
            $linhas[] = $doc->urlValidacao;
        }
        if ($doc->rodapeExtra !== '') {
            $linhas[] = '';
            $linhas[] = $doc->rodapeExtra;
        }

        return $linhas;
    }
}
