<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Validar documento · Exportação</title>
    <style>
        :root { --cor:#0a7a8a; --suave:#5b6b76; --linha:#cfd8df; --ok:#17663f; --ok-fundo:#e5f6ec; --erro:#9b2c2c; --erro-fundo:#fdeceb; }
        body { margin:0; font-family:"Segoe UI",sans-serif; background:linear-gradient(180deg,#f3f6f8,#e8edf0); color:#1a242b; }
        .wrap { max-width:640px; margin:40px auto; padding:0 16px; }
        .card { background:#fff; border:1px solid var(--linha); border-radius:14px; padding:20px; border-top:3px solid var(--cor); margin:0 0 14px; }
        h1 { margin:0 0 6px; font-size:1.25rem; color:var(--cor); }
        p { color:var(--suave); }
        label { display:grid; gap:4px; font-size:.8rem; font-weight:700; color:var(--suave); margin:0 0 10px; }
        input { min-height:40px; padding:8px 10px; border:1px solid var(--linha); border-radius:8px; font:inherit; text-transform:uppercase; }
        button { background:var(--cor); color:#fff; border:0; border-radius:10px; padding:10px 14px; font:inherit; cursor:pointer; }
        .badge { display:inline-flex; padding:3px 9px; border-radius:999px; font-size:.75rem; font-weight:700; }
        .ok { background:var(--ok-fundo); color:var(--ok); }
        .fail { background:var(--erro-fundo); color:var(--erro); }
        .warn { background:#fff6e8; color:#b86e00; }
        table { width:100%; border-collapse:collapse; margin-top:10px; }
        th, td { text-align:left; padding:8px; border-bottom:1px solid var(--linha); }
        a { color:var(--cor); }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <h1>Validar documento</h1>
        <p>Confira a autenticidade de um documento emitido pela exportação unificada (QR ou código impresso).</p>
        <form method="post" action="{{ url('/exportacao/validar') }}">
            @csrf
            <label>Código
                <input name="codigo" value="{{ $codigo ?? '' }}" required placeholder="Cole o código">
            </label>
            <button type="submit">Validar</button>
        </form>
    </div>

    @if(!empty($resultado))
        <div class="card" style="border-top-color:{{ !empty($resultado['ok']) ? 'var(--ok)' : 'var(--erro)' }}">
            <p>
                <span class="badge {{ !empty($resultado['ok']) ? 'ok' : 'fail' }}">
                    {{ !empty($resultado['ok']) ? 'Autêntico' : 'Inválido' }}
                </span>
                @if(!empty($resultado['via']))
                    <span class="badge warn">via {{ $resultado['via'] }}</span>
                @endif
            </p>
            <p>{{ $resultado['mensagem'] ?? '' }}</p>
            @if(!empty($resultado['documento']))
                <table>
                    <tr><th>Código</th><td>{{ $resultado['documento']['codigo'] ?? '—' }}</td></tr>
                    <tr><th>Tipo</th><td>{{ $resultado['documento']['tipo'] ?? '—' }}</td></tr>
                    <tr><th>Emitido em</th><td>{{ $resultado['documento']['emitido_em'] ?? '—' }}</td></tr>
                    @if(!empty($resultado['documento']['payload']['titulo']))
                        <tr><th>Título</th><td>{{ $resultado['documento']['payload']['titulo'] }}</td></tr>
                    @endif
                    @if(!empty($resultado['documento']['payload']['modulo']))
                        <tr><th>Módulo</th><td>{{ $resultado['documento']['payload']['modulo'] }}</td></tr>
                    @endif
                </table>
            @endif
        </div>
    @endif

    <p style="text-align:center;font-size:.85rem">
        @auth
            <a href="{{ url('/exportacao') }}">Painel da exportação</a>
        @else
            Exportação unificada · i-Educar
        @endauth
    </p>
</div>
</body>
</html>
