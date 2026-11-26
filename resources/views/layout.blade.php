<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo', 'Exportação')</title>
    <link rel="stylesheet" href="{{ url('/intranet/styles/font-awesome.css') }}">
    <style>
        :root {
            --cor: {{ $identidade['cor'] ?? '#0a7a8a' }};
            --destaque: {{ $identidade['destaque'] ?? '#1495a8' }};
            --clara: {{ $identidade['clara'] ?? '#e6f5f7' }};
            --nav: #15222b; --nav-texto: #e8eef3; --fundo: #e8edf0;
            --tinta: #1a242b; --suave: #5b6b76; --linha: #cfd8df;
            --ok: #17663f; --ok-fundo: #e5f6ec; --erro: #9b2c2c; --erro-fundo: #fdeceb;
            --cor-tela: var(--cor);
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: "Segoe UI", sans-serif; font-size: 14px; color: var(--tinta);
            background: linear-gradient(180deg, #f3f6f8, var(--fundo)); }
        .app { display: flex; min-height: 100vh; }
        .lateral { width: 248px; flex: none; background: linear-gradient(180deg, color-mix(in srgb, var(--cor) 35%, #15222b), var(--nav)); color: var(--nav-texto); }
        .marca { display: flex; align-items: center; justify-content: center; color: #fff; text-decoration: none; padding: 14px; border-bottom: 1px solid rgba(255,255,255,.08); }
        .icone { width: 40px; height: 40px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center;
            background: linear-gradient(145deg, var(--destaque), var(--cor)); color: #fff; }
        .modulo { padding: 8px 10px; }
        .modulo-nome { margin: 0; padding: 8px; color: #8aa0ad; font-size: 11px; letter-spacing: .08em; text-transform: uppercase; font-weight: 700; }
        .modulo a { display: flex; align-items: center; gap: 10px; color: var(--nav-texto); text-decoration: none; border-radius: 10px; padding: 8px 10px; }
        .modulo a i { width: 18px; text-align: center; }
        .modulo a:hover, .modulo a.ativo { background: color-mix(in srgb, var(--cor) 55%, #060b10); color: #fff; font-weight: 700; }
        .miolo { flex: 1; min-width: 0; display: flex; flex-direction: column; }
        .topo { min-height: 56px; display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 8px 18px; background: color-mix(in srgb,#fff 90%, transparent); border-bottom: 1px solid var(--linha); }
        main { padding: 18px 20px 28px; flex: 1; }
        .cabeca-tela { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin: -18px -20px 16px; padding: 14px 16px 14px 20px;
            background: linear-gradient(120deg, color-mix(in srgb, var(--clara) 70%, var(--mix-branco, #fff)), var(--superficie, #fff) 55%); border-bottom: 1px solid var(--linha); border-left: 4px solid var(--cor-tela); }
        .marca-tela { width: 46px; height: 46px; border-radius: 14px; display: grid; place-items: center; background: linear-gradient(145deg, var(--destaque), var(--cor-tela)); color: #fff; font-size: 18px; }
        .cabeca-tela h1 { margin: 0; font-size: 1.25rem; }
        .lead { margin: 2px 0 0; color: var(--suave); }
        .acoes-tela { display: flex; flex-wrap: wrap; gap: 8px; }
        .botao { display: inline-flex; align-items: center; gap: 8px; background: var(--cor-tela); color: #fff; border: 0; border-radius: 10px; padding: 8px 12px; font: inherit; text-decoration: none; cursor: pointer; }
        .botao.claro { background: var(--superficie, #fff); color: var(--cor-tela); border: 1px solid color-mix(in srgb, var(--cor-tela) 35%, var(--linha)); }
        .flash { border-radius: 12px; padding: 12px 14px; margin: 0 0 16px; }
        .flash.ok { background: var(--ok-fundo); color: var(--ok); }
        .flash.erro { background: var(--erro-fundo); color: var(--erro); }
        .bloco { background: var(--superficie, #fff); border: 1px solid var(--linha); border-radius: 14px; padding: 14px 16px; margin: 0 0 12px; border-top: 3px solid var(--cor-tela); }
        .bloco h2 { margin: 0 0 10px; display: flex; align-items: center; gap: 8px; font-size: 1.05rem; }
        .badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; border-radius: 999px; font-size: .75rem; font-weight: 700; }
        .badge.ok { background: var(--ok-fundo); color: var(--ok); }
        .badge.warn { background: #fff6e8; color: #b86e00; }
        .badge.fail { background: var(--erro-fundo); color: var(--erro); }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 8px 10px; border-bottom: 1px solid var(--linha); }
        th { background: var(--clara); color: var(--cor); }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px; align-items: end; }
        .form-grid label { display: grid; gap: 4px; font-size: .8rem; font-weight: 700; color: var(--suave); }
        .form-grid input, .form-grid select { min-height: 36px; padding: 6px 8px; border: 1px solid var(--linha); border-radius: 8px; font: inherit; }
        .form-grid .col-wide { grid-column: 1 / -1; }
        .form-grid .acoes { grid-column: 1 / -1; display: flex; justify-content: flex-end; gap: 8px; }
        .chips { display: flex; flex-wrap: wrap; gap: 6px; }
        .chip { padding: 4px 10px; border-radius: 999px; background: var(--clara); color: var(--cor); font-size: .78rem; font-weight: 700; }
        @media (max-width: 860px) { .lateral { display: none; } }
    </style>
    @include('buriti-ui::estilos')
</head>
<body>
@php $grupos = $menuGrupos ?? []; @endphp
<div class="app">
    <aside class="lateral">
        <a class="marca" href="{{ url('/exportacao') }}" title="{{ $identidade['nome'] ?? 'Exportação' }}">
            <span class="icone"><i class="fa {{ $identidade['icone'] ?? 'fa-share-square-o' }}"></i></span>
        </a>
        @foreach($grupos as $grupo)
            <nav class="modulo">
                <div class="modulo-nome">{{ $grupo['nome'] }}</div>
                @foreach($grupo['itens'] as $item)
                    <a href="{{ url($item['url']) }}" class="@if(request()->is(ltrim($item['url'],'/'))) ativo @endif">
                        <i class="fa {{ $item['icone'] }}"></i> {{ $item['rotulo'] }}
                    </a>
                @endforeach
            </nav>
        @endforeach
    </aside>
    <div class="miolo">
        @include('buriti-ui::topo')
        <main>
            @if(session('ok')) <p class="flash ok">{{ session('ok') }}</p> @endif
            @if(session('erro')) <p class="flash erro">{{ session('erro') }}</p> @endif
            <header class="cabeca-tela">
                <div style="display:flex;align-items:center;gap:12px">
                    <span class="marca-tela"><i class="fa @yield('icone-tela', $identidade['icone'] ?? 'fa-share-square-o')"></i></span>
                    <div>
                        <h1>@yield('titulo-tela', 'Exportação')</h1>
                        @hasSection('lead')<p class="lead">@yield('lead')</p>@endif
                    </div>
                </div>
                <div class="acoes-tela">
                    @yield('acoes-topo')
                    @includeWhen(View::exists('buriti-ui::informes-modulo'), 'buriti-ui::informes-modulo')
                </div>
            </header>
            @yield('conteudo')
        </main>
        @include('buriti-ui::rodape', ['moduloRodape' => 'Exportação'])
    </div>
</div>
</body>
</html>
