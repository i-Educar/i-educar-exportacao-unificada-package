<?php

namespace iEducar\Packages\BuritiExportacaoUnificada\Http\Controllers;

use iEducar\Packages\BuritiExportacaoUnificada\Services\EmissorDocumento;
use iEducar\Packages\BuritiExportacaoUnificada\Services\Exportador;
use iEducar\Packages\BuritiExportacaoUnificada\Services\ValidadorDocumentoBridge;
use iEducar\Packages\BuritiExportacaoUnificada\Support\Formato;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class ExportacaoController extends Controller
{
    public function index(): View
    {
        $exportador = app(Exportador::class);
        $validador = app(ValidadorDocumentoBridge::class);

        return view('exportacao::painel', [
            'formatos' => $exportador->formatosDisponiveis(),
            'formatosPadrao' => app(EmissorDocumento::class)->formatosPadrao(),
            'formatosRelatorio' => array_map(static fn (Formato $f) => $f->value, Formato::relatorioTr()),
            'formatosLista' => array_map(static fn (Formato $f) => $f->value, Formato::listaTr()),
            'institucional' => (array) config('exportacao.institucional', []),
            'validacao' => [
                'habilitado' => $validador->habilitado(),
                'advanced' => $validador->advancedReportsDisponivel(),
            ],
        ]);
    }

    public function salvarConfig(Request $request): RedirectResponse
    {
        // Persistência leve em .env não é feita aqui — configura via config/env.
        // A tela serve para visualizar e testar; alteração operacional via ENV.
        return redirect('/exportacao')
            ->with('ok', 'Configuração operacional via ENV/config (EXPORTACAO_*). Use a demonstração para validar a emissão.');
    }

    public function demo(): View
    {
        return view('exportacao::demo', [
            'formatos' => app(Exportador::class)->formatosDisponiveis(),
            'padrao' => app(EmissorDocumento::class)->formatosPadrao(),
        ]);
    }

    public function emitirDemo(Request $request): Response|RedirectResponse
    {
        $formato = strtolower(trim((string) $request->input('formato', 'pdf')));
        $comValidacao = $request->boolean('validacao', true);
        $usuario = Auth::user();
        $nome = $usuario?->name ?? $usuario?->login ?? 'sistema';

        try {
            Formato::from($formato);
        } catch (\ValueError) {
            return back()->with('erro', 'Formato inválido: '.$formato);
        }

        $tabela = Exportador::amostraOnda0();
        $emissor = app(EmissorDocumento::class);

        return $emissor->download(
            $tabela,
            $formato,
            'Exportação unificada · PoC',
            $nome,
            $comValidacao,
            'prova-exportacao-'.$formato,
        );
    }

    public function validarForm(): View
    {
        return view('exportacao::validar', [
            'resultado' => null,
            'codigo' => '',
        ]);
    }

    public function validar(Request $request, ?string $codigo = null): View
    {
        $codigo = $codigo ?? (string) $request->input('codigo', '');
        $resultado = app(ValidadorDocumentoBridge::class)->validar($codigo);

        return view('exportacao::validar', [
            'resultado' => $resultado,
            'codigo' => strtoupper(trim($codigo)),
        ]);
    }
}
