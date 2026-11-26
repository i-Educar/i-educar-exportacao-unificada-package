{{--
  PDF no modelo de declaração dos Relatórios Avançados:
  reutiliza cabeçalho/rodapé/QR de advanced-reports::pdf.* sem alterar aquele pacote.
--}}
@extends('advanced-reports::pdf.layout')

@section('doc_title', $title ?? ($doc->titulo ?? 'Exportação'))
@section('doc_subtitle', $subtitle ?? ($doc->subtitulo ?? ''))
@section('formal_header', '1')
@section('doc_municipality', $municipality ?? ($doc->entidade ?? ''))
@section('doc_school', $schoolName ?? ($doc->secretaria ?? ''))
@section('doc_contact', $contact ?? ($doc->sistema ?? 'i-Educar'))

@section('content')
@php
  $cor = $cor ?? '#0a7a8a';
  $tabela = $tabela ?? null;
  $doc = $doc ?? null;
@endphp
<style>
  .ex-borda {
    position: fixed;
    top: -98px;
    left: -22px;
    right: -22px;
    bottom: -28px;
    border: 2.5px solid {{ $cor }};
    pointer-events: none;
  }
  .ex-faixa {
    height: 6px;
    background: {{ $cor }};
    margin: 0 0 12px;
  }
  .ex-titulo {
    text-align: center;
    color: {{ $cor }};
    font-size: 16px;
    letter-spacing: .04em;
    text-transform: uppercase;
    margin: 4px 0 6px;
  }
  .ex-meta {
    text-align: center;
    color: #5b6b76;
    font-size: 9px;
    margin: 0 0 14px;
  }
  .ex-tabela th {
    background: {{ $cor }} !important;
    color: #fff !important;
    border-color: {{ $cor }} !important;
    font-size: 8px;
    text-transform: uppercase;
  }
  .ex-tabela td { font-size: 9px; }
  .ex-tabela tr:nth-child(even) td { background: #f3fafb; }
  .ex-selo {
    display: inline-block;
    margin: 0 0 10px;
    padding: 3px 8px;
    border: 1px solid {{ $cor }};
    color: {{ $cor }};
    border-radius: 4px;
    font-size: 8px;
    font-weight: 700;
    text-transform: uppercase;
  }
</style>

<div class="ex-borda"></div>
<div class="ex-faixa"></div>

<div style="text-align:center">
  <span class="ex-selo">Documento oficial · exportação unificada</span>
</div>

<h1 class="ex-titulo">{{ $title ?? ($doc->titulo ?? 'Exportação') }}</h1>
<p class="ex-meta">
  @if(!empty($doc?->moduloOrigem))Módulo: {{ $doc->moduloOrigem }} · @endif
  @if(!empty($doc?->emitidoPor))Emitido por {{ $doc->emitidoPor }} · @endif
  {{ $issuedAt ?? ($doc->emitidoEm ?? date('d/m/Y H:i')) }}
</p>

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

@if(!empty($doc?->assinaturas))
  <div class="ex-assinaturas" style="page-break-inside: avoid; margin: 20px 0 8px;">
    <strong style="color: {{ $cor }}; display: block; margin: 0 0 4px; font-size: 10px; text-transform: uppercase; letter-spacing: .04em;">Assinaturas</strong>
    <table style="margin: 0; border: 0; width: 100%; border-collapse: separate; border-spacing: 12px 0;">
      @foreach(array_chunk($doc->assinaturas, 2) as $par)
        <tr>
          @foreach($par as $a)
            @php
              $nomeAssina = trim((string) ($a['nome'] ?? ''));
              $quandoAssina = trim((string) ($a['quando'] ?? ''));
            @endphp
            <td style="border: 0; text-align: center; vertical-align: bottom; padding: 42px 14px 10px; width: 50%;">
              <div style="border-top: 1px solid #1a242b; margin: 0 8px 8px;"></div>
              <strong style="font-size: 10px; color: #1a242b;">{{ $nomeAssina !== '' ? $nomeAssina : '________________' }}</strong><br>
              <span class="muted" style="font-size: 9px;">{{ $a['cargo'] ?? 'Responsável' }}</span>
              @if($quandoAssina !== '')
                <br><span class="muted" style="font-size: 8px;">{{ $quandoAssina }}</span>
              @endif
            </td>
          @endforeach
          @if(count($par) === 1)
            <td style="border: 0; width: 50%;"></td>
          @endif
        </tr>
      @endforeach
    </table>
  </div>
@endif

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
@endsection
