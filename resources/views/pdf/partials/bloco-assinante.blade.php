{{-- Minimalista: nome · cargo · Conferir autenticidade (validade) --}}
@php
  $nome = trim((string) ($assinante['nome'] ?? $assinante['displayName'] ?? ''));
  $cargo = trim((string) ($assinante['cargo'] ?? 'Responsável'));
  $quando = trim((string) ($assinante['quando'] ?? ''));
  $status = trim((string) ($assinante['status'] ?? ''));
  // Autenticidade da assinatura: só o código próprio do signatário (não o do documento).
  $codigo = trim((string) ($assinante['codigo_validacao'] ?? ''));
  $url = trim((string) ($assinante['url_validacao'] ?? ''));
  $cor = $cor ?? '#0a7a8a';
@endphp
<div style="text-align:center;padding-top:14px;page-break-inside:avoid;">
  <div style="border-top:1px solid #1a242b;margin:0 4px 4px;height:0;"></div>
  <strong style="font-size:8.5px;color:#1a242b;line-height:1.25;">{{ $nome !== '' ? $nome : '________________' }}</strong><br>
  <span style="font-size:7.5px;color:#374151;">{{ $cargo }}</span>
  @if($quando !== '')
    <br><span style="font-size:7px;color:#374151;">{{ $quando }}</span>
  @endif
  @if($status !== '')
    <br><span style="font-size:7px;color:#5b6b76;">{{ $status }}</span>
  @elseif($nome !== '' && class_exists(\iEducar\Packages\BuritiAssinaturaDigital\Services\AssinaturaOrquestrador::class))
    <br><span style="font-size:7px;color:{{ $cor }};">Assinatura digital</span>
  @endif
  @if($nome !== '' && ($codigo !== '' || $url !== ''))
    @php $label = 'Conferir'.($codigo !== '' ? ' · '.$codigo : ''); @endphp
    <br>
    @if($url !== '')
      <a href="{{ $url }}" style="font-size:6.5px;color:{{ $cor }};text-decoration:none;border-bottom:1px dotted {{ $cor }};">{{ $label }}</a>
    @else
      <span style="font-size:6.5px;color:{{ $cor }};">{{ $label }}</span>
    @endif
  @endif
</div>
