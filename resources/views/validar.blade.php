<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,nofollow">
    <title>Validar documento · Exportação</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700">
    <link rel="stylesheet" href="{{ url('/intranet/styles/font-awesome.css') }}">
    <link rel="stylesheet" href="{{ url('/intranet/styles/login.css') }}">
    <style>
        :root {
            --cor:#0a7a8a; --destaque:#1a9aab; --suave:#5b6b76; --linha:#cfd8df; --tinta:#1a242b;
            --ok:#17663f; --ok-fundo:#e5f6ec; --erro:#9b2c2c; --erro-fundo:#fdeceb;
            --warn:#b86e00; --warn-fundo:#fff4e0;
        }
        * { box-sizing: border-box; }
        body.login-scene {
            margin: 0; min-height: 100vh;
            font-family: "Open Sans", "Segoe UI", sans-serif; font-size: 14px; color: var(--tinta);
        }
        .login-scene-veil { pointer-events: none; }
        #main.auth-public-main {
            width: min(640px, calc(100% - 24px)) !important;
            max-width: 640px;
            margin: 0 auto;
            padding: 12px 0 calc(28px + env(safe-area-inset-bottom, 0px));
            text-align: left;
        }
        .auth-nav { display: flex; justify-content: flex-end; margin: 0 0 10px; }
        .auth-nav a {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 8px 12px; border-radius: 999px;
            border: 1px solid color-mix(in srgb, var(--cor) 25%, #fff);
            background: rgba(255,255,255,.92); color: var(--cor);
            text-decoration: none; font-weight: 700; font-size: .84rem;
            box-shadow: 0 6px 16px rgba(15,30,40,.08);
        }
        .auth-shell {
            background: #fff; border: 1px solid var(--linha); border-radius: 16px;
            box-shadow: 0 18px 40px rgba(15,30,40,.12); overflow: hidden;
        }
        .auth-head {
            padding: 16px; color: #fff;
            background: linear-gradient(125deg, color-mix(in srgb, var(--cor) 92%, #000), var(--destaque));
        }
        .auth-head h1 { margin: 0; font-size: clamp(1.05rem, 3.6vw, 1.3rem); font-weight: 800; }
        .auth-head p { margin: 6px 0 0; opacity: .92; font-size: .84rem; line-height: 1.4; }
        .auth-body { padding: 14px 16px 18px; }
        label { display: block; font-size: .72rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; color: var(--suave); margin: 0 0 6px; }
        .linha-form { display: flex; gap: 8px; align-items: stretch; }
        input {
            flex: 1; min-width: 0; min-height: 44px; padding: 10px 12px;
            border: 1px solid var(--linha); border-radius: 10px; font: inherit;
            text-transform: uppercase; color: var(--tinta);
        }
        button {
            display: inline-flex; align-items: center; justify-content: center; gap: 7px;
            min-height: 44px; border: 0; border-radius: 10px; background: var(--cor);
            color: #fff; padding: 10px 14px; font: inherit; font-weight: 700; cursor: pointer; white-space: nowrap;
        }
        .veredicto { display: flex; gap: 12px; align-items: flex-start; border-radius: 12px; padding: 14px; margin: 0 0 10px; }
        .veredicto .ico { width: 48px; height: 48px; border-radius: 14px; display: grid; place-items: center; color: #fff; font-size: 1.4rem; flex: none; }
        .veredicto h2 { margin: 0 0 4px; font-size: 1.1rem; }
        .veredicto p { margin: 0; color: inherit; }
        .veredicto.ok { background: var(--ok-fundo); color: var(--ok); }
        .veredicto.ok .ico { background: var(--ok); }
        .veredicto.erro { background: var(--erro-fundo); color: var(--erro); }
        .veredicto.erro .ico { background: var(--erro); }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid var(--linha); font-size: .88rem; }
        .legenda { display: grid; gap: 6px; font-size: .82rem; color: var(--suave); margin-top: 12px; }
        .rodape { margin-top: 14px; text-align: center; color: var(--suave); font-size: .76rem; }
        .rodape a { color: var(--cor); font-weight: 700; text-decoration: none; }
        @media (max-width: 560px) {
            body.login-scene { background-attachment: scroll; }
            .linha-form { flex-direction: column; }
            button { width: 100%; }
            #main.auth-public-main { width: min(100% - 16px, 640px) !important; }
            .auth-body { padding: 12px; }
            .login-brand-logo { max-height: 56px !important; }
            .login-brand-name { font-size: 1.05rem !important; }
        }
    </style>
</head>
<body class="login-scene">
@php
    $ok = !empty($resultado['ok']);
    $encontrado = !empty($resultado['documento']) || $ok || (($resultado['mensagem'] ?? '') !== '');
    $nivel = $ok ? 'ok' : 'erro';
    $titulo = $ok ? 'Documento válido' : (!empty($resultado['documento']) ? 'Documento suspeito' : 'Código não encontrado');
    $resumo = $ok
        ? 'Este documento foi emitido neste sistema e o conteúdo confere. Você pode confiar nele.'
        : (!empty($resultado['documento'])
            ? 'O código existe, mas o conteúdo não confere com o original. Peça uma nova emissão.'
            : ((string) ($resultado['mensagem'] ?? 'Não encontramos este código. Confira se digitou certo.')));
    $entidade = config('legacy.config.ieducar_entity_name')
        ?? config('legacy.app.entity.name')
        ?? 'Secretaria Municipal de Educação';
    $logo = config('legacy.config.ieducar_image') ?? url('intranet/imagens/brasao-republica.png');
@endphp
<div class="login-scene-veil" aria-hidden="true"></div>
<div id="main" class="auth-public-main">
    <nav class="auth-nav">
        <a href="{{ url('/login') }}" title="Voltar à tela de login">
            <i class="fa fa-home" aria-hidden="true"></i> Início / Login
        </a>
    </nav>

    <header class="login-brand">
        <img class="login-brand-logo" alt="Logo" src="{{ $logo }}">
        <h1 class="login-brand-name">{{ $entidade }}</h1>
        <p class="login-brand-tag">Validação de autenticidade</p>
    </header>

    <div class="auth-shell">
        <header class="auth-head">
            <h1><i class="fa fa-shield"></i> Este documento é verdadeiro?</h1>
            <p>Digite o código do rodapé ou do QR. Verde = válido, vermelho = inválido.</p>
        </header>
        <div class="auth-body">
            <form method="post" action="{{ url('/exportacao/validar') }}">
                @csrf
                <label for="codigo">Código de autenticidade</label>
                <div class="linha-form">
                    <input id="codigo" name="codigo" value="{{ $codigo ?? '' }}" required placeholder="Cole o código" autocomplete="off">
                    <button type="submit"><i class="fa fa-search"></i> Verificar</button>
                </div>
            </form>
            @if(empty($resultado))
                <div class="legenda">
                    <div><i class="fa fa-check-circle" style="color:var(--ok)"></i> Verde — pode confiar</div>
                    <div><i class="fa fa-times-circle" style="color:var(--erro)"></i> Vermelho — não use como prova</div>
                </div>
            @endif

            @if(!empty($resultado))
                <div style="margin-top:14px">
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
        </div>
    </div>

    <p class="rodape">
        <a href="{{ url('/login') }}"><i class="fa fa-home"></i> Voltar ao login</a>
        · <a href="{{ url('/assinatura-digital/verificar') }}">Assinatura digital</a>
        @auth
            · <a href="{{ url('/exportacao') }}">Painel da exportação</a>
        @endauth
    </p>
</div>
</body>
</html>
