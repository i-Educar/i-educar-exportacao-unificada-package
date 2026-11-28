<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Validar documento · Exportação</title>
    <link rel="stylesheet" href="{{ url('/intranet/styles/font-awesome.css') }}">
    <style>
        :root {
            --cor:#0a7a8a; --suave:#5b6b76; --linha:#cfd8df; --tinta:#1a242b;
            --ok:#17663f; --ok-fundo:#e5f6ec; --erro:#9b2c2c; --erro-fundo:#fdeceb;
            --warn:#b86e00; --warn-fundo:#fff4e0;
        }
        body { margin:0; font-family:"Segoe UI",sans-serif; background:linear-gradient(180deg,#f3f6f8,#e8edf0); color:var(--tinta); }
        .wrap { max-width:640px; margin:40px auto; padding:0 16px; }
        .card { background:#fff; border:1px solid var(--linha); border-radius:14px; padding:20px; border-top:3px solid var(--cor); margin:0 0 14px; }
        h1 { margin:0 0 6px; font-size:1.25rem; color:var(--cor); }
        p { color:var(--suave); }
        label { display:grid; gap:4px; font-size:.8rem; font-weight:700; color:var(--suave); margin:0 0 10px; }
        input { min-height:40px; padding:8px 10px; border:1px solid var(--linha); border-radius:8px; font:inherit; text-transform:uppercase; }
        button { background:var(--cor); color:#fff; border:0; border-radius:10px; padding:10px 14px; font:inherit; cursor:pointer; display:inline-flex; gap:8px; align-items:center; }
        .veredicto { display:flex; gap:12px; align-items:flex-start; border-radius:12px; padding:14px; margin:0 0 10px; }
        .veredicto .ico { width:48px; height:48px; border-radius:14px; display:grid; place-items:center; color:#fff; font-size:1.4rem; flex:none; }
        .veredicto h2 { margin:0 0 4px; font-size:1.1rem; }
        .veredicto p { margin:0; color:inherit; }
        .veredicto.ok { background:var(--ok-fundo); color:var(--ok); }
        .veredicto.ok .ico { background:var(--ok); }
        .veredicto.erro { background:var(--erro-fundo); color:var(--erro); }
        .veredicto.erro .ico { background:var(--erro); }
        .veredicto.warn { background:var(--warn-fundo); color:#8a5a00; }
        .veredicto.warn .ico { background:var(--warn); }
        table { width:100%; border-collapse:collapse; margin-top:10px; }
        th, td { text-align:left; padding:8px; border-bottom:1px solid var(--linha); }
        a { color:var(--cor); }
        .legenda { display:grid; gap:6px; font-size:.82rem; color:var(--suave); margin-top:12px; }
    </style>
</head>
<body>
@php
    $ok = !empty($resultado['ok']);
    $encontrado = !empty($resultado['documento']) || $ok || (($resultado['mensagem'] ?? '') !== '');
    $nivel = $ok ? 'ok' : ($encontrado && !empty($resultado['documento']) ? 'erro' : 'erro');
    $titulo = $ok ? 'Documento válido' : (!empty($resultado['documento']) ? 'Documento suspeito' : 'Código não encontrado');
    $resumo = $ok
        ? 'Este documento foi emitido neste sistema e o conteúdo confere. Você pode confiar nele.'
        : (!empty($resultado['documento'])
            ? 'O código existe, mas o conteúdo não confere com o original. Peça uma nova emissão.'
            : ((string) ($resultado['mensagem'] ?? 'Não encontramos este código. Confira se digitou certo.')));
@endphp
<div class="wrap">
    <div class="card">
        <h1><i class="fa fa-shield"></i> Este documento é verdadeiro?</h1>
        <p>Digite o código do rodapé ou do QR. O resultado aparece em cores: verde = válido, vermelho = inválido.</p>
        <form method="post" action="{{ url('/exportacao/validar') }}">
            @csrf
            <label>Código de autenticidade
                <input name="codigo" value="{{ $codigo ?? '' }}" required placeholder="Cole o código">
            </label>
            <button type="submit"><i class="fa fa-search"></i> Verificar</button>
        </form>
        @if(empty($resultado))
            <div class="legenda">
                <div><i class="fa fa-check-circle" style="color:var(--ok)"></i> Verde — pode confiar</div>
                <div><i class="fa fa-times-circle" style="color:var(--erro)"></i> Vermelho — não use como prova</div>
            </div>
        @endif
    </div>

    @if(!empty($resultado))
        <div class="card" style="border-top-color:{{ $nivel === 'ok' ? 'var(--ok)' : 'var(--erro)' }}">
            <div class="veredicto {{ $nivel }}">
                <div class="ico"><i class="fa {{ $nivel === 'ok' ? 'fa-check-circle' : 'fa-times-circle' }}"></i></div>
                <div>
                    <h2>{{ $titulo }}</h2>
                    <p>{{ $resumo }}</p>
                </div>
            </div>
            @if(!empty($resultado['documento']))
                <table>
                    <tr><th>Código</th><td>{{ $resultado['documento']['codigo'] ?? '—' }}</td></tr>
                    <tr><th>Tipo</th><td>{{ $resultado['documento']['tipo'] ?? '—' }}</td></tr>
                    <tr><th>Emitido em</th><td>{{ $resultado['documento']['emitido_em'] ?? '—' }}</td></tr>
                    @if(!empty($resultado['documento']['payload']['titulo']))
                        <tr><th>Título</th><td>{{ $resultado['documento']['payload']['titulo'] }}</td></tr>
                    @endif
                </table>
            @endif
        </div>
    @endif

    <p style="text-align:center;font-size:.85rem">
        @auth
            <a href="{{ url('/exportacao') }}">Painel da exportação</a>
            ·
        @endauth
        <a href="{{ url('/assinatura-digital/verificar') }}">Validador público (assinatura digital)</a>
    </p>
</div>
</body>
</html>
