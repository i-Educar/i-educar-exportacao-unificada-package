{{--
  PDF no modelo de declaração dos Relatórios Avançados:
  reutiliza cabeçalho/rodapé/QR de advanced-reports::pdf.* sem alterar aquele pacote.
--}}
@extends('advanced-reports::pdf.layout')

@section('doc_title', $title ?? ($doc->titulo ?? 'Exportação'))
@section('doc_subtitle', '')
@section('formal_header', '1')
@section('doc_municipality', $municipality ?? ($doc->entidade ?? ''))
@section('doc_school', $schoolName ?? '')
@section('doc_contact', $contact ?? '')
@section('disable_footer', '1')

@section('content')
@php
  $cor = $cor ?? '#0a7a8a';
  $tabela = $tabela ?? null;
  $doc = $doc ?? null;
  $nLinhas = $tabela ? count($tabela->matriz()) : 0;
  $assinaturas = is_array($doc?->assinaturas ?? null) ? $doc->assinaturas : [];
  $nAssinaturas = count($assinaturas);
  /*
   Empurra assinaturas+validação para o fim da 1ª página sem estourar o fecho
   para a 2ª. O cabeçalho institucional (~128px) reduz a área útil: reserva
   explícita do fecho (assinaturas + QR) evita page-break do bloco inteiro.
  */
  $reservaFechoMm = min(92, max(58, 42 + $nAssinaturas * 9));
  $utilMioloMm = 142; // A4 − cabeçalho alto − margem inferior − folga
  $alturaTabelaEstMm = 26 + max(0, $nLinhas) * 6.2; // selo+título+thead+linhas
  $alturaConteudoMm = (int) max(
      (int) ceil($alturaTabelaEstMm),
      (int) floor($utilMioloMm - $reservaFechoMm)
  );
  // Só força 2ª página quando a tabela em si já ocupa quase toda a 1ª.
  $fechoNovaPagina = $nLinhas > 28;
@endphp
<style>
  /*
   Cabeçalho com etapas fica mais alto — margem superior maior evita a borda
   do .ar-header cortar o miolo (selo / título / tabela).
  */
  @page { margin: 118px 36px 36px 36px !important; }
  /* Card cinza do cabeçalho alinhado às margens laterais do documento */
  .ar-header {
    top: -100px !important;
    left: -36px !important;
    right: -36px !important;
    border: 1px solid #ddd !important;
    border-left: 0 !important;
    border-right: 0 !important;
    border-bottom-width: 1px !important;
    padding: 0 36px !important;
    box-sizing: border-box !important;
  }
  .ar-content { padding-bottom: 4px !important; }

  .ex-borda {
    position: fixed;
    top: -106px;
    left: -36px;
    right: -36px;
    bottom: -18px;
    border: 2.5px solid {{ $cor }};
    pointer-events: none;
    z-index: -1;
  }
  .ex-faixa {
    height: 4px;
    background: {{ $cor }};
    margin: 0 0 6px;
  }
  .ex-titulo {
    text-align: center;
    color: {{ $cor }};
    font-size: 14px;
    letter-spacing: .04em;
    text-transform: uppercase;
    margin: 2px 0 8px;
  }
  .ex-tabela { margin-bottom: 0; }
  .ex-tabela th {
    background: {{ $cor }} !important;
    color: #fff !important;
    border-color: {{ $cor }} !important;
    font-size: 8px;
    text-transform: uppercase;
  }
  .ex-tabela td { font-size: 9px; padding: 3px 4px; }
  .ex-tabela tr:nth-child(even) td { background: #f3fafb; }
  .ex-selo {
    display: inline-block;
    margin: 0 0 6px;
    padding: 2px 7px;
    border: 1px solid {{ $cor }};
    color: {{ $cor }};
    border-radius: 4px;
    font-size: 8px;
    font-weight: 700;
    text-transform: uppercase;
  }
  .ex-miolo {
    min-height: {{ max(24, $alturaConteudoMm) }}mm;
  }
  .ex-fecho {
    page-break-inside: avoid;
    page-break-before: avoid;
    margin-top: 6px;
  }
  .ex-assinaturas {
    page-break-inside: avoid;
    margin: 0 0 4px;
  }
  .ex-validacao-bloco {
    page-break-inside: avoid;
  }
  .ex-validacao-bloco .ar-official-footer,
  .ex-validacao-bloco > div {
    margin-top: 8px !important;
    padding-top: 6px !important;
  }
</style>

<div class="ex-borda"></div>

<div class="ex-miolo">
  <div class="ex-faixa"></div>

  <div style="height: 0.45em;" aria-hidden="true">&nbsp;</div>

  <div style="text-align:center">
    <span class="ex-selo">Documento oficial · exportação unificada</span>
  </div>

  <h1 class="ex-titulo">{{ $title ?? ($doc->titulo ?? 'Exportação') }}</h1>

  @php
    $exSub = trim((string) ($doc->subtitulo ?? ($tabela->subtitulo ?? '')));
  @endphp
  @if($exSub !== '')
    <div class="ex-sub" style="text-align:center;margin:0 0 12px;color:#374151;font-size:9.5px;line-height:1.45;">
      {!! nl2br(e($exSub)) !!}
    </div>
  @endif

  @if($tabela)
    <table class="ex-tabela">
      <thead>
        <tr>
          @foreach($tabela->cabecalhos as $c)
            <th>{{ $c }}</th>
          @endforeach
        </tr>
      </thead>
      <tbody>
        @forelse($tabela->matriz() as $linha)
          <tr>
            @foreach($linha as $cel)
              <td>{{ $cel }}</td>
            @endforeach
          </tr>
        @empty
          <tr>
            <td colspan="{{ max(1, count($tabela->cabecalhos)) }}">Sem registros para exportar.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  @endif
</div>

<div class="ex-fecho" @if($fechoNovaPagina) style="page-break-before: always;" @endif>
  @if($assinaturas !== [])
    <div class="ex-assinaturas">
      <strong style="color: {{ $cor }}; display: block; margin: 0 0 6px; font-size: 10px; text-transform: uppercase; letter-spacing: .04em;">Assinaturas</strong>
      <table style="margin: 0; border: 0; width: 100%; border-collapse: separate; border-spacing: 8px 0;">
        <tr>
          @foreach($assinaturas as $a)
            <td style="border: 0; text-align: center; vertical-align: bottom; padding: 0 4px 8px; width: {{ max(12, (int) floor(100 / max(1, $nAssinaturas))) }}%;">
              @include('exportacao::pdf.partials.bloco-assinante', [
                'assinante' => $a,
                'codigoValidacao' => null,
                'urlValidacao' => null,
                'cor' => $cor,
              ])
            </td>
          @endforeach
        </tr>
      </table>
    </div>
  @endif

  <div class="ex-validacao-bloco">
  @include('advanced-reports::student-documents._footer', [
    'footerInline' => true,
    'issuedAt' => $issuedAt ?? ($doc->emitidoEm ?? date('d/m/Y H:i')),
    'issuerName' => $issuerName ?? ($doc->emitidoPor ?? null),
    'issuerRole' => $issuerRole ?? null,
    'cityUf' => $cityUf ?? ($municipality ?? ($doc->entidade ?? null)),
    'validationCode' => $validationCode ?? ($doc->codigoValidacao ?? null),
    'validationUrl' => $validationUrl ?? ($doc->urlValidacao ?? null),
    'qrDataUri' => $qrDataUri ?? ($doc->qrDataUri ?? null),
  ])
  </div>
</div>
@endsection
