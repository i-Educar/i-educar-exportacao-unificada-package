<?php

namespace iEducar\Packages\BuritiExportacaoUnificada\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use iEducar\Packages\AdvancedReports\Services\PdfRenderService;
use iEducar\Packages\BuritiExportacaoUnificada\Support\ArquivoExportado;
use iEducar\Packages\BuritiExportacaoUnificada\Support\DocumentoInstitucional;
use iEducar\Packages\BuritiExportacaoUnificada\Support\Formato;
use iEducar\Packages\BuritiExportacaoUnificada\Support\TabelaExportacao;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * API pública para todos os módulos Buriti (exceto Relatórios avançados).
 *
 * Emite PDF/Excel/demais formatos TR 4.2/4.3 com layout institucional
 * e QR/link do validador de documentos.
 */
final class EmissorDocumento
{
    public function __construct(
        private readonly Exportador $exportador = new Exportador(),
        private readonly LayoutInstitucional $layout = new LayoutInstitucional(),
        private readonly ValidadorDocumentoBridge $validador = new ValidadorDocumentoBridge(),
    ) {
    }

    /**
     * @param  list<Formato|string>|Formato|string  $formato
     * @param  list<array{nome?:string,cargo:string,quando?:?string}>|null  $assinaturas
     * @return ArquivoExportado|list<ArquivoExportado>
     */
    public function emitir(
        TabelaExportacao $tabela,
        Formato|string|array $formato = Formato::Pdf,
        string $moduloOrigem = '',
        ?string $emitidoPor = null,
        bool $comValidacao = true,
        ?array $assinaturas = null,
        ?string $nomeBase = null,
    ): ArquivoExportado|array {
        $doc = DocumentoInstitucional::fromConfig(
            titulo: $tabela->titulo,
            subtitulo: $tabela->subtitulo,
            moduloOrigem: $moduloOrigem,
            emitidoPor: $emitidoPor,
            comValidacao: $comValidacao,
            assinaturas: $assinaturas,
        );

        if ($doc->comValidacao) {
            $tipo = (string) config('exportacao.validacao.tipo_documento', 'exportacao_buriti');
            $reg = $this->validador->registrar($tipo, [
                'titulo' => $tabela->titulo,
                'subtitulo' => $tabela->subtitulo,
                'modulo' => $moduloOrigem,
                'linhas' => count($tabela->linhas),
                'emitido_por' => $emitidoPor,
            ]);
            if ($reg['codigo'] !== '') {
                $doc = $doc->comValidacaoEmitida($reg['codigo'], $reg['url'], $reg['qr_data_uri']);
            }
        }

        $htmlInstitucional = $this->layout->html($tabela, $doc);
        $linhasTexto = $this->layout->linhasTexto($tabela, $doc);

        if (is_array($formato)) {
            $saida = [];
            foreach ($formato as $fmt) {
                $saida[] = $this->gerarUm($tabela, $fmt, $doc, $htmlInstitucional, $linhasTexto, $nomeBase);
            }

            return $saida;
        }

        return $this->gerarUm($tabela, $formato, $doc, $htmlInstitucional, $linhasTexto, $nomeBase);
    }

    /**
     * Resposta HTTP pronta (download) — uso típico nos Controllers dos módulos.
     */
    /**
     * @param  list<array{nome?:string,cargo:string,quando?:?string}>|null  $assinaturas
     */
    public function download(
        TabelaExportacao $tabela,
        Formato|string $formato = Formato::Pdf,
        string $moduloOrigem = '',
        ?string $emitidoPor = null,
        bool $comValidacao = true,
        ?string $nomeBase = null,
        ?array $assinaturas = null,
    ): Response {
        $arquivo = $this->emitir(
            $tabela,
            $formato,
            $moduloOrigem,
            $emitidoPor,
            $comValidacao,
            $assinaturas,
            $nomeBase,
        );
        assert($arquivo instanceof ArquivoExportado);

        return response($arquivo->conteudo, 200, [
            'Content-Type' => $arquivo->mime(),
            'Content-Disposition' => 'attachment; filename="'.$arquivo->nomeArquivo().'"',
        ]);
    }

    /** @return list<string> */
    public function formatosPadrao(): array
    {
        $cfg = (array) config('exportacao.formatos_padrao', ['pdf', 'xlsx', 'csv', 'html']);

        return array_values(array_filter($cfg, static fn ($f) => is_string($f) && $f !== ''));
    }

    private function gerarUm(
        TabelaExportacao $tabela,
        Formato|string $formato,
        DocumentoInstitucional $doc,
        string $htmlInstitucional,
        array $linhasTexto,
        ?string $nomeBase,
    ): ArquivoExportado {
        $fmt = $formato instanceof Formato ? $formato : Formato::from(strtolower((string) $formato));

        // HTML/PDF/TXT usam layout institucional; planilhas e OOXML mantêm drivers tabulares.
        if ($fmt === Formato::Html) {
            return new ArquivoExportado($htmlInstitucional, $fmt, $nomeBase ?? $tabela->titulo);
        }

        if ($fmt === Formato::Pdf) {
            return new ArquivoExportado(
                $this->pdfDeclaracao($tabela, $doc, $linhasTexto),
                $fmt,
                $nomeBase ?? $tabela->titulo,
            );
        }

        if ($fmt === Formato::Txt) {
            return new ArquivoExportado(
                implode("\n", $linhasTexto)."\n",
                $fmt,
                $nomeBase ?? $tabela->titulo,
            );
        }

        return $this->exportador->exportar($tabela, $fmt, $nomeBase);
    }

    /**
     * PDF no modelo de declaração (Relatórios Avançados): borda, cabeçalho, logos, rodapé e QR.
     * Não altera o pacote Serventec — só consome PdfRenderService + views pdf.* / _footer.
     *
     * @param  list<string>  $linhasTexto
     */
    private function pdfDeclaracao(TabelaExportacao $tabela, DocumentoInstitucional $doc, array $linhasTexto): string
    {
        $dados = [
            'title' => $doc->titulo,
            'subtitle' => $doc->subtitulo !== '' ? $doc->subtitulo : $tabela->subtitulo,
            'municipality' => $doc->entidade,
            'schoolName' => $doc->secretaria,
            'contact' => $doc->sistema.($doc->moduloOrigem !== '' ? ' · '.$doc->moduloOrigem : ''),
            'issuedAt' => $doc->emitidoEm,
            'issuerName' => $doc->emitidoPor,
            'validationCode' => $doc->codigoValidacao,
            'validationUrl' => $doc->urlValidacao,
            'qrDataUri' => $doc->qrDataUri,
            'tabela' => $tabela,
            'doc' => $doc,
            'cor' => (string) config('exportacao.institucional.cor', '#0a7a8a'),
        ];

        try {
            if (class_exists(PdfRenderService::class)) {
                return app(PdfRenderService::class)->renderToString(
                    'exportacao::pdf.tabela-declaracao',
                    $dados,
                );
            }

            if (class_exists(Dompdf::class)) {
                $html = view('exportacao::pdf.tabela-declaracao', $dados)->render();
                // Evita deadlock no artisan serve: não buscar logos via HTTP no mesmo host.
                $html = preg_replace_callback(
                    '/(<img\b[^>]*\bsrc=["\'])([^"\']+)(["\'])/i',
                    static function (array $m): string {
                        $src = html_entity_decode($m[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        if (str_starts_with($src, 'data:')) {
                            return $m[0];
                        }
                        $bases = array_values(array_unique(array_filter([
                            rtrim((string) config('app.url'), '/'),
                            rtrim((string) url('/'), '/'),
                        ])));
                        $abs = null;
                        foreach ($bases as $base) {
                            if ($base !== '' && str_starts_with($src, $base.'/')) {
                                $candidate = public_path(ltrim(substr($src, strlen($base)), '/'));
                                if (is_file($candidate)) {
                                    $abs = $candidate;
                                    break;
                                }
                            }
                        }
                        if ($abs === null && ! str_starts_with($src, 'http://') && ! str_starts_with($src, 'https://')) {
                            $candidate = public_path(ltrim((string) (parse_url($src, PHP_URL_PATH) ?: $src), '/'));
                            if (is_file($candidate)) {
                                $abs = $candidate;
                            }
                        }
                        if ($abs === null || ! is_readable($abs)) {
                            return $m[0];
                        }
                        $mime = @mime_content_type($abs) ?: 'image/png';
                        $bin = @file_get_contents($abs);
                        if ($bin === false || $bin === '') {
                            return $m[0];
                        }

                        return $m[1].'data:'.$mime.';base64,'.base64_encode($bin).$m[3];
                    },
                    $html
                ) ?? $html;
                $options = new Options();
                $options->set('isRemoteEnabled', true);
                $options->set('isHtml5ParserEnabled', true);
                $options->setChroot(public_path());
                $dompdf = new Dompdf($options);
                $dompdf->setPaper('a4', 'portrait');
                $dompdf->loadHtml($html, 'UTF-8');
                $dompdf->render();

                return $dompdf->output();
            }
        } catch (Throwable $e) {
            report($e);
        }

        return $this->pdfDasLinhas($linhasTexto);
    }

    /** @param  list<string>  $linhas */
    private function pdfDasLinhas(array $linhas): string
    {
        $win = static function (string $texto): string {
            $map = [
                'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
                'é' => 'e', 'ê' => 'e', 'è' => 'e',
                'í' => 'i', 'ì' => 'i',
                'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
                'ú' => 'u', 'ü' => 'u',
                'ç' => 'c', 'ñ' => 'n',
                'Á' => 'A', 'À' => 'A', 'Ã' => 'A', 'Â' => 'A',
                'É' => 'E', 'Ê' => 'E',
                'Í' => 'I',
                'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O',
                'Ú' => 'U',
                'Ç' => 'C',
            ];

            return strtr($texto, $map);
        };
        $pdfString = static function (string $texto): string {
            return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $texto);
        };

        $y = 800;
        $ops = ["BT /F1 10 Tf 40 {$y} Td"];
        foreach ($linhas as $i => $linha) {
            $safe = $pdfString($win($linha));
            $ops[] = $i === 0 ? "({$safe}) Tj" : "0 -12 Td ({$safe}) Tj";
        }
        $ops[] = 'ET';
        $stream = implode("\n", $ops);

        $objs = [];
        $objs[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objs[2] = '<< /Type /Pages /Kids [3 0 R] /Count 1 >>';
        $objs[3] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>';
        $objs[4] = '<< /Length '.strlen($stream)." >>\nstream\n{$stream}\nendstream";
        $objs[5] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objs as $n => $body) {
            $offsets[$n] = strlen($pdf);
            $pdf .= "{$n} 0 obj\n{$body}\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objs) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($objs); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= 'trailer'."\n<< /Size ".(count($objs) + 1)." /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xref}\n%%EOF\n";

        return $pdf;
    }
}
