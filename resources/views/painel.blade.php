@extends('exportacao::layout')
@section('titulo', 'Exportação')
@section('icone-tela', 'fa-cog')
@section('titulo-tela', 'Exportação unificada')
@section('lead')
    Motor compartilhado TR 4.2/4.3 — PDF, Excel e demais formatos, com layout institucional e QR do validador.
    Relatórios avançados (Serventec) permanecem à parte.
@endsection
@section('acoes-topo')
    <a class="botao" href="{{ url('/exportacao/demo') }}"><i class="fa fa-play"></i> Demonstração</a>
    <a class="botao claro" href="{{ url('/exportacao/validar') }}"><i class="fa fa-qrcode"></i> Validar</a>
@endsection

@section('conteudo')
    <div class="bloco">
        <h2><i class="fa fa-building-o"></i> Identidade institucional</h2>
        <table>
            <tr><th>Entidade</th><td>{{ $institucional['entidade'] ?? '—' }}</td></tr>
            <tr><th>Secretaria</th><td>{{ $institucional['secretaria'] ?? '—' }}</td></tr>
            <tr><th>Sistema</th><td>{{ $institucional['sistema'] ?? '—' }}</td></tr>
            <tr><th>Rodapé</th><td>{{ $institucional['rodape_extra'] ?? '—' }}</td></tr>
            <tr><th>Assinaturas</th>
                <td>
                    @if(!empty($institucional['mostrar_assinaturas']))
                        {{ implode(' · ', $institucional['cargos_assinatura'] ?? []) }}
                    @else
                        desligadas
                    @endif
                </td>
            </tr>
            <tr><th>Logos</th>
                <td>
                    Esq: {{ ($institucional['logo_esquerda'] ?? '') !== '' ? 'configurado' : 'padrão' }}
                    · Dir: {{ ($institucional['logo_direita'] ?? '') !== '' ? 'configurado' : 'padrão' }}
                </td>
            </tr>
        </table>
        <p style="margin:12px 0 0;color:var(--suave);font-size:.85rem">
            Ajuste via ENV: <code>EXPORTACAO_ENTIDADE</code>, <code>EXPORTACAO_SECRETARIA</code>,
            <code>EXPORTACAO_LOGO_ESQ</code>, <code>EXPORTACAO_LOGO_DIR</code>,
            <code>EXPORTACAO_CARGOS_ASSINATURA</code>, <code>EXPORTACAO_VALIDACAO</code>.
        </p>
    </div>

    <div class="bloco">
        <h2><i class="fa fa-qrcode"></i> Validador de documentos</h2>
        <p style="margin:0 0 10px">
            @if(!empty($validacao['habilitado']))
                <span class="badge ok"><i class="fa fa-check"></i> Habilitado</span>
            @else
                <span class="badge fail"><i class="fa fa-ban"></i> Desligado</span>
            @endif
            @if(!empty($validacao['advanced']))
                <span class="badge ok"><i class="fa fa-link"></i> Conectado aos Relatórios avançados</span>
            @else
                <span class="badge warn"><i class="fa fa-database"></i> Fallback local (/exportacao/validar)</span>
            @endif
        </p>
        <p style="margin:0;color:var(--suave);font-size:.85rem">
            Cada emissão institucional registra código + QR + link público.
            Se o pacote de Relatórios avançados estiver ativo, usa
            <code>/documentos/validar/{code}</code>; senão, a rota local da exportação.
        </p>
    </div>

    <div class="bloco">
        <h2><i class="fa fa-files-o"></i> Formatos do edital</h2>
        <p style="margin:0 0 8px;font-weight:700;color:var(--suave);font-size:.78rem;text-transform:uppercase">4.2 Relatórios</p>
        <div class="chips" style="margin-bottom:12px">
            @foreach($formatosRelatorio as $f)
                <span class="chip">{{ strtoupper($f) }}</span>
            @endforeach
        </div>
        <p style="margin:0 0 8px;font-weight:700;color:var(--suave);font-size:.78rem;text-transform:uppercase">4.3 Listas</p>
        <div class="chips" style="margin-bottom:12px">
            @foreach($formatosLista as $f)
                <span class="chip">{{ strtoupper($f) }}</span>
            @endforeach
        </div>
        <p style="margin:0 0 8px;font-weight:700;color:var(--suave);font-size:.78rem;text-transform:uppercase">Padrão da PoC</p>
        <div class="chips">
            @foreach($formatosPadrao as $f)
                <span class="chip">{{ strtoupper($f) }}</span>
            @endforeach
        </div>
    </div>

    <div class="bloco">
        <h2><i class="fa fa-code"></i> Uso nos módulos</h2>
        <pre style="margin:0;padding:12px;background:#f7fbfc;border-radius:10px;overflow:auto;font-size:.8rem">use iEducar\Packages\BuritiExportacaoUnificada\Services\EmissorDocumento;
use iEducar\Packages\BuritiExportacaoUnificada\Support\Formato;
use iEducar\Packages\BuritiExportacaoUnificada\Support\TabelaExportacao;

return app(EmissorDocumento::class)->download(
    new TabelaExportacao('Alunos', ['Nome','Turma'], [['Ana','1A']]),
    Formato::Pdf,
    'Escolar',
    auth()->user()?->name,
);</pre>
        <p style="margin:10px 0 0;color:var(--suave);font-size:.85rem">
            Não use este motor dentro de Relatórios avançados — aquele pacote já emite PDF/Excel próprios.
        </p>
    </div>
@endsection
