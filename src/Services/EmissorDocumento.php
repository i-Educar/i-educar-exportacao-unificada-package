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
    /**
     * @param  array{codigo?:string,url?:string,qr_data_uri?:?string}|null  $validacaoExistente
     */
    public function emitir(
        TabelaExportacao $tabela,
        Formato|string|array $formato = Formato::Pdf,
        string $moduloOrigem = '',
        ?string $emitidoPor = null,
        bool $comValidacao = true,
        ?array $assinaturas = null,
        ?string $nomeBase = null,
        ?array $validacaoExistente = null,
    ): ArquivoExportado|array {
        $doc = DocumentoInstitucional::fromConfig(
            titulo: $tabela->titulo,
            subtitulo: $tabela->subtitulo,
            moduloOrigem: $moduloOrigem,
            emitidoPor: $emitidoPor,
            comValidacao: $comValidacao,
            assinaturas: $assinaturas,
        );

        $codExistente = trim((string) ($validacaoExistente['codigo'] ?? ''));
        $urlExistente = trim((string) ($validacaoExistente['url'] ?? ''));
        if ($codExistente !== '' && $urlExistente !== '') {
            $qr = $validacaoExistente['qr_data_uri'] ?? null;
            if (! is_string($qr) || $qr === '') {
                $qr = $this->validador->qrParaUrl($urlExistente);
            }
            $doc = $doc->comValidacaoEmitida($codExistente, $urlExistente, is_string($qr) ? $qr : null);
            $doc = $doc->comCodigosAssinaturasIndividuais($this->validador);
        } elseif ($doc->comValidacao) {
            $tipo = (string) config('exportacao.validacao.tipo_documento', 'exportacao_buriti');
            $reg = $this->validador->registrar($tipo, [
                'titulo' => $tabela->titulo,
                'subtitulo' => $tabela->subtitulo,
                'modulo' => $moduloOrigem,
                'linhas' => count($tabela->linhas),
                'emitido_por' => $emitidoPor,
                'assinaturas' => $doc->assinaturas,
            ]);
            if ($reg['codigo'] !== '') {
                $doc = $doc->comValidacaoEmitida($reg['codigo'], $reg['url'], $reg['qr_data_uri']);
            }
            // Código individual por assinatura (≠ código do documento no rodapé).
            $doc = $doc->comCodigosAssinaturasIndividuais($this->validador);
        }

        if (is_array($formato)) {
            $saida = [];
            foreach ($formato as $fmt) {
                $saida[] = $this->gerarEEncaminhar($tabela, $fmt, $doc, $moduloOrigem, $nomeBase);
            }

            return $saida;
        }

        return $this->gerarEEncaminhar($tabela, $formato, $doc, $moduloOrigem, $nomeBase);
    }

    /**
     * Gera o arquivo; no PDF com LibreSign, regenera com URL do validador LibreSign sob cada assinatura.
     */
    private function gerarEEncaminhar(
        TabelaExportacao $tabela,
        Formato|string $formato,
        DocumentoInstitucional $doc,
        string $moduloOrigem,
        ?string $nomeBase,
    ): ArquivoExportado {
        $htmlInstitucional = $this->layout->html($tabela, $doc);
        $linhasTexto = $this->layout->linhasTexto($tabela, $doc);
        $arquivo = $this->gerarUm($tabela, $formato, $doc, $htmlInstitucional, $linhasTexto, $nomeBase);

        $ls = $this->encaminharLibreSignSeAplicavel($arquivo, $doc, $moduloOrigem);
        if ($ls === null) {
            return $arquivo;
        }

        $urlLs = (string) ($ls['url_libresign'] ?? '');
        $uuid = (string) ($ls['uuid'] ?? '');
        if ($urlLs === '') {
            return $arquivo;
        }

        if (class_exists(\iEducar\Packages\BuritiAssinaturaDigital\Support\BlocoAssinatura::class)) {
            $doc = $doc->comAssinaturas(
                \iEducar\Packages\BuritiAssinaturaDigital\Support\BlocoAssinatura::comUrlLibreSign(
                    $doc->assinaturas,
                    $urlLs,
                ),
                (int) ($ls['pedido_id'] ?? 0) ?: null,
            );
        }

        $htmlInstitucional = $this->layout->html($tabela, $doc);
        $linhasTexto = $this->layout->linhasTexto($tabela, $doc);
        $arquivoFinal = $this->gerarUm($tabela, $formato, $doc, $htmlInstitucional, $linhasTexto, $nomeBase);

        if ($uuid !== '' && class_exists(\iEducar\Packages\BuritiAssinaturaDigital\Services\LibreSign\LibreSignClient::class)) {
            try {
                app(\iEducar\Packages\BuritiAssinaturaDigital\Services\LibreSign\LibreSignClient::class)
                    ->substituirPdf($uuid, $arquivoFinal->conteudo, $arquivoFinal->nomeArquivo());
            } catch (Throwable $e) {
                report($e);
            }
        }

        return $arquivoFinal;
    }

    /**
     * @return array{uuid:string,url_libresign:string,pedido_id:int}|null
     */
    private function encaminharLibreSignSeAplicavel(ArquivoExportado $arquivo, DocumentoInstitucional $doc, string $moduloOrigem): ?array
    {
        if (! (bool) config('exportacao.assinatura_digital.habilitado', true)) {
            return null;
        }
        if ($doc->assinaturas === []) {
            return null;
        }
        if ($arquivo->formato !== Formato::Pdf) {
            return null;
        }
        if (! class_exists(\iEducar\Packages\BuritiAssinaturaDigital\Services\AssinaturaOrquestrador::class)) {
            return null;
        }
        if (! str_starts_with($arquivo->conteudo, '%PDF')) {
            return null;
        }

        try {
            $orch = app(\iEducar\Packages\BuritiAssinaturaDigital\Services\AssinaturaOrquestrador::class);
            $res = $orch->tentarSolicitarDeDocumento(
                $doc->titulo,
                $arquivo->conteudo,
                $doc->assinaturas,
                [
                    'modulo_origem' => $moduloOrigem,
                    'codigo_validacao_exportacao' => $doc->codigoValidacao,
                    'url_validacao_exportacao' => $doc->urlValidacao,
                    'origem' => 'exportacao_unificada',
                ],
                $arquivo->nomeArquivo(),
            );
            if ($res === null) {
                return null;
            }
            $pedido = is_array($res['pedido'] ?? null) ? $res['pedido'] : [];
            $uuid = (string) ($pedido['libresign_uuid'] ?? '');
            $urlLs = '';
            if ($uuid !== '' && class_exists(\iEducar\Packages\BuritiAssinaturaDigital\Services\LibreSign\LibreSignClient::class)) {
                $urlLs = app(\iEducar\Packages\BuritiAssinaturaDigital\Services\LibreSign\LibreSignClient::class)
                    ->urlValidacao($uuid);
            }

            return [
                'uuid' => $uuid,
                'url_libresign' => $urlLs,
                'pedido_id' => (int) ($pedido['id'] ?? 0),
            ];
        } catch (Throwable $e) {
            if (! (bool) config('exportacao.assinatura_digital.falhar_silencioso', true)) {
                throw $e;
            }
            report($e);

            return null;
        }
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
        $headerEtapas = [];
        foreach ($doc->assinaturas as $a) {
            if (! is_array($a)) {
                continue;
            }
            $headerEtapas[] = [
                'rotulo' => (string) ($a['cargo'] ?? 'Etapa'),
                'quando' => trim((string) ($a['quando'] ?? '')) !== ''
                    ? (string) $a['quando']
                    : (trim((string) ($a['nome'] ?? '')) !== '' ? 'Registrada' : '—'),
            ];
        }

        $brand = DocumentoInstitucional::brandingDoIeducar();
        $prefeitura = $brand['prefeitura'];
        if (trim($doc->entidade) !== '' && ! preg_match('/^i-educar/i', trim($doc->entidade))) {
            // Preferir entidade do doc quando for prefeitura/município real
            if (preg_match('/^prefeitura\b/ui', $doc->entidade) || str_contains(mb_strtolower($doc->entidade), 'municipal')) {
                $prefeitura = trim($doc->entidade);
            }
        }
        // SME: preferir branding do i-Educar (nm_responsavel / cidade)
        $secretaria = $brand['secretaria'];
        if (trim($doc->secretaria) !== ''
            && ! preg_match('/^secretaria municipal de educa(ç|c)ão$/ui', trim($doc->secretaria))
        ) {
            $secretaria = trim($doc->secretaria);
        }

        $dados = [
            'title' => $doc->titulo,
            'subtitle' => '',
            'municipality' => $prefeitura,
            'schoolName' => $secretaria,
            'contact' => $brand['contato'],
            'headerBrand' => null,
            'headerModule' => $this->rotuloModuloInstitucional($doc->moduloOrigem),
            'headerEtapas' => $headerEtapas,
            'issuedAt' => $doc->emitidoEm,
            'issuerName' => $doc->emitidoPor,
            'validationCode' => $doc->codigoValidacao,
            'validationUrl' => $doc->urlValidacao,
            'qrDataUri' => $doc->qrDataUri,
            'tabela' => $tabela,
            'doc' => $doc,
            'cor' => (string) config('exportacao.institucional.cor', '#0a7a8a'),
        ];

        // 1) Relatórios avançados (PdfRenderService) — identidade visual oficial.
        if (class_exists(PdfRenderService::class)) {
            try {
                $bin = app(PdfRenderService::class)->renderToString(
                    'exportacao::pdf.tabela-declaracao',
                    $dados,
                    'a4',
                    'portrait',
                    false,
                );
                if ($this->pdfTemVisualInstitucional($bin)) {
                    return $bin;
                }
            } catch (Throwable $e) {
                report($e);
            }
        }

        // 2) Dompdf direto com a mesma view — se o PdfRender falhar (Blade/ParseError etc.).
        if (class_exists(Dompdf::class)) {
            try {
                $bin = $this->pdfViaDompdf($dados);
                if ($this->pdfTemVisualInstitucional($bin)) {
                    return $bin;
                }
            } catch (Throwable $e) {
                report($e);
            }
        }

        // 3) Último recurso: texto (nunca deve ser o caminho normal).
        return $this->pdfDasLinhas($linhasTexto);
    }

    /** @param  array<string, mixed>  $dados */
    private function pdfViaDompdf(array $dados): string
    {
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

        return (string) $dompdf->output();
    }

    private function pdfTemVisualInstitucional(string $bin): bool
    {
        if ($bin === '' || ! str_starts_with($bin, '%PDF')) {
            return false;
        }
        // Fallback tipográfico cru do pdfDasLinhas — não tem layout Dompdf.
        if (str_contains($bin, 'BT /F1 10 Tf')) {
            return false;
        }

        return strlen($bin) > 4000;
    }

    private function rotuloModuloInstitucional(string $moduloOrigem): string
    {
        $m = mb_strtolower(trim($moduloOrigem));
        if ($m === '') {
            return 'Exportação';
        }

        return match (true) {
            str_contains($m, 'almox') => 'Almoxarifado escolar',
            str_contains($m, 'aliment') => 'Alimentação escolar',
            str_contains($m, 'transport') => 'Transporte escolar',
            str_contains($m, 'bibliotec') => 'Biblioteca',
            str_contains($m, 'solicit') || str_contains($m, 'chamado') => 'Chamados e solicitações',
            str_contains($m, 'atendiment') => 'Atendimento ao estudante',
            str_contains($m, 'contrat') => 'Contratações',
            str_contains($m, 'advanced') || str_contains($m, 'report') || str_contains($m, 'relator') => 'Relatórios avançados',
            str_contains($m, 'servidor') || $m === 'rh' || str_contains($m, 'rh-') => 'Servidores',
            str_contains($m, 'horario') => 'Horários escolares',
            str_contains($m, 'evento') || str_contains($m, 'formacao') => 'Eventos e formação',
            str_contains($m, 'censo') || str_contains($m, 'educacenso') => 'Educacenso',
            str_contains($m, 'export') => 'Exportação de documentos',
            str_contains($m, 'assinatura') => 'Assinatura digital',
            default => str_replace(['-', '_'], ' ', $moduloOrigem),
        };
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
