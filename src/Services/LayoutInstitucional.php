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
            $validacaoHtml = '<table class="validacao"><tr>'
                .($qr !== '' ? '<td style="width:100px;">'.$qr.'</td>' : '')
                .'<td><strong>Autenticidade do documento</strong>'
                .'<p style="margin:4px 0;">Código: <code>'.$e((string) $doc->codigoValidacao).'</code></p>'
                .'<p class="url">'.$e((string) $doc->urlValidacao).'</p>'
                .'<p class="hint">Escaneie o QR ou abra o link — verde na tela = documento válido.</p>'
                .$lsHint
                .'</td></tr></table>';
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
  @page { size: A4; margin: 10mm 12mm; }
  body { margin:0; font-family:"Segoe UI",Arial,sans-serif; color:var(--tinta); font-size:12px; }
  .folha { max-width:900px; margin:0 auto; padding:16px 18px 20px; }
  .cabeca { display:table; width:100%; border-bottom:3px solid var(--cor); padding-bottom:10px; margin-bottom:12px; }
  .cabeca .logos, .cabeca > div { display:table-cell; vertical-align:middle; }
  .cabeca .logos { width:1%; white-space:nowrap; padding-right:12px; }
  .logo { max-height:48px; max-width:96px; object-fit:contain; }
  .logo-placeholder { width:44px; height:44px; border-radius:10px; background:#0a7a8a; color:#fff; text-align:center; line-height:44px; font-size:1.2rem; font-weight:800; }
  .cabeca h1 { margin:0 0 2px; font-size:.92rem; color:var(--cor); text-transform:uppercase; letter-spacing:.04em; }
  .cabeca .entidade { margin:0; font-size:1.05rem; font-weight:800; }
  .cabeca .meta { margin:2px 0 0; color:var(--suave); font-size:.75rem; word-break:break-word; }
  .titulo-doc { margin:0 0 4px; font-size:1.15rem; }
  .sub { margin:0 0 10px; color:var(--suave); font-size:.85rem; white-space:pre-line; word-break:break-word; }
  table.dados { width:100%; border-collapse:collapse; margin:0 0 12px; }
  table.dados th, table.dados td { border:1px solid var(--linha); padding:5px 7px; text-align:left; word-break:break-word; }
  table.dados th { background:#e6f5f7; color:var(--cor); font-size:.72rem; text-transform:uppercase; letter-spacing:.03em; }
  .assinaturas { margin:12px 0 4px; page-break-inside:auto; }
  .assinaturas h2, .validacao strong { color:var(--cor); font-size:.78rem; margin:0 0 4px; text-transform:uppercase; letter-spacing:.04em; }
  .grade-assina, .ls-grade-assina { width:100%; border-collapse:collapse; table-layout:fixed; }
  .assina, .ls-assina { text-align:center; padding-top:10px; vertical-align:top; word-break:break-word; }
  .linha-assina { border-top:1px solid #1a242b; margin:0 10px 6px; height:0; }
  .assina strong, .ls-assina strong { display:block; font-size:.78rem; color:var(--tinta); word-break:break-word; }
  .assina span, .assina small, .ls-assina span, .ls-assina small { display:block; color:var(--suave); font-size:.68rem; line-height:1.25; word-break:break-word; }
  .assina .ls-auth, .ls-assina .ls-auth { display:inline-block; margin-top:4px; font-size:.62rem; color:var(--cor); text-decoration:none; border-bottom:1px dotted var(--cor); word-break:break-all; }
  .validacao { width:100%; border-collapse:collapse; margin:12px 0; padding:0; border:1px dashed #9eb8be; border-radius:8px; background:#f7fbfc; page-break-inside:avoid; }
  .validacao td { padding:8px 10px; vertical-align:middle; }
  .validacao .qr { border:1px solid var(--linha); border-radius:6px; background:#fff; width:88px; height:88px; }
  .validacao .url { word-break:break-all; font-size:.68rem; color:var(--suave); }
  .validacao .hint { margin:3px 0 0; font-size:.68rem; color:var(--suave); }
  .rodape { margin-top:14px; padding-top:8px; border-top:1px solid var(--linha); color:var(--suave); font-size:.68rem; }
  .rodape span { display:inline-block; margin-right:12px; }
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

  <table class="dados">
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
