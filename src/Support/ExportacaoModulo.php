<?php

namespace iEducar\Packages\BuritiExportacaoUnificada\Support;

use iEducar\Packages\BuritiExportacaoUnificada\Services\EmissorDocumento;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ponte única: módulos de origem montam dados; este helper pede geração ao 4.x.
 * Relatórios avançados (Serventec) ficam de fora — não usar daqui.
 */
final class ExportacaoModulo
{
    public static function disponivel(): bool
    {
        return class_exists(EmissorDocumento::class);
    }

    /**
     * @param  list<string>  $cabecalhos
     * @param  list<list<scalar|null>>  $linhas
     * @param  list<array{nome?:string,cargo:string,quando?:?string}>|null  $assinaturas
     */
    public static function download(
        string $titulo,
        array $cabecalhos,
        array $linhas,
        string $moduloOrigem,
        string $formato = 'pdf',
        ?string $subtitulo = null,
        ?string $emitidoPor = null,
        ?string $nomeBase = null,
        bool $comValidacao = true,
        ?array $assinaturas = null,
    ): Response|RedirectResponse {
        if (! self::disponivel()) {
            return redirect()->back()->with('erro', 'Módulo de exportação unificada não está instalado.');
        }

        // Modal envia validacao=0|1; PDF institucional exige QR/código (padrão do validador).
        $formatoNorm = strtolower(trim($formato)) ?: 'pdf';
        if ($formatoNorm === 'pdf') {
            $comValidacao = true;
        } elseif (function_exists('request') && request() && request()->has('validacao')) {
            $comValidacao = request()->boolean('validacao');
        }

        $tabela = new TabelaExportacao(
            $titulo,
            $cabecalhos,
            $linhas,
            $subtitulo ?? $moduloOrigem,
        );

        $user = auth()->user();
        $emitidoPor ??= $user
            ? (string) ($user->name ?? $user->nome ?? $user->email ?? 'usuário')
            : null;

        return app(EmissorDocumento::class)->download(
            $tabela,
            strtolower(trim($formato)) ?: 'pdf',
            $moduloOrigem,
            $emitidoPor,
            $comValidacao,
            $nomeBase ?? self::slug($moduloOrigem.'-'.$titulo),
            $assinaturas,
        );
    }

    /** @return list<string> */
    public static function formatosPadrao(): array
    {
        if (! self::disponivel()) {
            return ['pdf', 'csv', 'xlsx'];
        }

        return app(EmissorDocumento::class)->formatosPadrao();
    }

    private static function slug(string $texto): string
    {
        $s = mb_strtolower($texto);
        $s = preg_replace('/[^a-z0-9]+/u', '-', $s) ?? 'export';

        return trim($s, '-') ?: 'export';
    }
}
