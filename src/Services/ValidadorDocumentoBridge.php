<?php

namespace iEducar\Packages\BuritiExportacaoUnificada\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Ponte com o validador de documentos dos Relatórios avançados.
 * Se o pacote Serventec não estiver carregado, usa armazenamento local (PoC).
 */
final class ValidadorDocumentoBridge
{
    public const string CACHE_PREFIX = 'exportacao_doc_valid_';

    public function habilitado(): bool
    {
        return (bool) config('exportacao.validacao.habilitado', true);
    }

    public function advancedReportsDisponivel(): bool
    {
        return (bool) config('exportacao.validacao.preferir_advanced_reports', true)
            && class_exists(\iEducar\Packages\AdvancedReports\Services\DocumentSigningService::class)
            && class_exists(\iEducar\Packages\AdvancedReports\Models\AdvancedReportsDocument::class);
    }

    /**
     * @param  array<string,mixed>  $payload
     * @return array{codigo:string,url:string,qr_data_uri:?string,via:string,mac:?string}
     */
    public function registrar(string $tipo, array $payload): array
    {
        if (! $this->habilitado()) {
            return [
                'codigo' => '',
                'url' => '',
                'qr_data_uri' => null,
                'via' => 'desabilitado',
                'mac' => null,
            ];
        }

        if ($this->advancedReportsDisponivel()) {
            try {
                return $this->viaAdvancedReports($tipo, $payload);
            } catch (Throwable) {
                // cai no fallback local
            }
        }

        return $this->viaLocal($tipo, $payload);
    }

    /**
     * @return array{ok:bool,via:string,documento:?array<string,mixed>,mensagem:string}
     */
    public function validar(string $codigo): array
    {
        $codigo = strtoupper(trim($codigo));
        if ($codigo === '') {
            return ['ok' => false, 'via' => '', 'documento' => null, 'mensagem' => 'Informe o código do documento.'];
        }

        if ($this->advancedReportsDisponivel()) {
            try {
                $doc = \iEducar\Packages\AdvancedReports\Models\AdvancedReportsDocument::query()
                    ->where('code', $codigo)
                    ->first();
                if ($doc) {
                    $signing = app(\iEducar\Packages\AdvancedReports\Services\DocumentSigningService::class);
                    $payload = is_array($doc->payload) ? $doc->payload : [];
                    $ok = $signing->verifyStoredDocument(
                        (string) $doc->mac,
                        (string) $doc->code,
                        (string) $doc->type,
                        $doc->issued_at,
                        $payload,
                    );
                    if (! $ok) {
                        $ok = $this->tentarRepararMac($doc, $signing, $payload);
                        if ($ok) {
                            $payload = is_array($doc->payload) ? $doc->payload : $payload;
                        }
                    }

                    return [
                        'ok' => $ok,
                        'via' => 'advanced-reports',
                        'documento' => [
                            'codigo' => $doc->code,
                            'tipo' => $doc->type,
                            'emitido_em' => optional($doc->issued_at)->format('d/m/Y H:i:s'),
                            'payload' => $payload,
                        ],
                        'mensagem' => $ok
                            ? 'Documento autêntico (validador de relatórios avançados).'
                            : 'Código encontrado, mas a assinatura HMAC não confere.',
                    ];
                }
            } catch (Throwable) {
                // tenta local
            }
        }

        $local = Cache::get(self::CACHE_PREFIX.$codigo);
        if (! is_array($local)) {
            return [
                'ok' => false,
                'via' => 'local',
                'documento' => null,
                'mensagem' => 'Documento não encontrado. Verifique o código ou o QR.',
            ];
        }

        $esperado = (string) ($local['mac'] ?? '');
        $calc = $this->macLocal($codigo, (string) ($local['tipo'] ?? ''), (string) ($local['issued_at'] ?? ''), (array) ($local['payload'] ?? []));
        $ok = $esperado !== '' && hash_equals($esperado, $calc);

        return [
            'ok' => $ok,
            'via' => 'local',
            'documento' => $ok ? $local : null,
            'mensagem' => $ok
                ? 'Documento autêntico (validador local da exportação unificada).'
                : 'Código local inválido ou adulterado.',
        ];
    }

    /**
     * @param  array<string,mixed>  $payload
     * @return array{codigo:string,url:string,qr_data_uri:?string,via:string,mac:?string}
     */
    private function viaAdvancedReports(string $tipo, array $payload): array
    {
        $signing = app(\iEducar\Packages\AdvancedReports\Services\DocumentSigningService::class);
        $code = $signing->generateCode(8);
        $issuedAt = now();
        $issuedAtIso = \iEducar\Packages\AdvancedReports\Services\DocumentSigningService::issuedAtForMac($issuedAt);
        $url = method_exists($signing, 'validationUrl')
            ? $signing->validationUrl($code)
            : route('advanced-reports.documents.validate', ['code' => $code]);

        $payloadToStore = array_merge($payload, ['validation_url' => $url]);
        $mac = $signing->mac($code, $tipo, $issuedAtIso, $payloadToStore);

        \iEducar\Packages\AdvancedReports\Models\AdvancedReportsDocument::query()->create([
            'code' => $code,
            'type' => $tipo,
            'issued_at' => $issuedAt,
            'mac' => $mac,
            'version' => \iEducar\Packages\AdvancedReports\Services\DocumentSigningService::VERSION,
            'payload' => $payloadToStore,
            'issued_by_user_id' => Auth::id(),
            'issued_ip' => request()?->ip(),
            'issued_user_agent' => substr((string) request()?->userAgent(), 0, 255),
        ]);

        return [
            'codigo' => $code,
            'url' => $url,
            'qr_data_uri' => $this->qrDataUri($url),
            'via' => 'advanced-reports',
            'mac' => $mac,
        ];
    }

    /**
     * @param  array<string,mixed>  $payload
     * @return array{codigo:string,url:string,qr_data_uri:?string,via:string,mac:?string}
     */
    private function viaLocal(string $tipo, array $payload): array
    {
        $code = strtoupper(bin2hex(random_bytes(8)));
        $issuedAt = now()->utc()->format('Y-m-d\TH:i:s\Z');
        $url = class_exists(\iEducar\Packages\BuritiAssinaturaDigital\Services\ValidacaoPublicaService::class)
            ? app(\iEducar\Packages\BuritiAssinaturaDigital\Services\ValidacaoPublicaService::class)->urlPublica($code)
            : url('/exportacao/validar/'.$code);
        $payloadToStore = array_merge($payload, ['validation_url' => $url]);
        $mac = $this->macLocal($code, $tipo, $issuedAt, $payloadToStore);

        Cache::put(self::CACHE_PREFIX.$code, [
            'codigo' => $code,
            'tipo' => $tipo,
            'issued_at' => $issuedAt,
            'mac' => $mac,
            'payload' => $payloadToStore,
            'emitido_em' => now()->format('d/m/Y H:i:s'),
        ], now()->addDays(365));

        return [
            'codigo' => $code,
            'url' => $url,
            'qr_data_uri' => $this->qrDataUri($url),
            'via' => 'local',
            'mac' => $mac,
        ];
    }

    /** @param  array<string,mixed>  $payload */
    private function macLocal(string $code, string $tipo, string $issuedAt, array $payload): string
    {
        ksort($payload);
        $data = json_encode([
            'v' => 1,
            'code' => $code,
            'type' => $tipo,
            'issued_at' => $issuedAt,
            'payload' => $payload,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';

        return hash_hmac('sha256', $data, (string) config('app.key', 'exportacao-local'));
    }

    /**
     * @param  array<string,mixed>  $payload
     */
    private function tentarRepararMac(
        \iEducar\Packages\AdvancedReports\Models\AdvancedReportsDocument $doc,
        \iEducar\Packages\AdvancedReports\Services\DocumentSigningService $signing,
        array $payload,
    ): bool {
        if ($doc->issued_at === null) {
            return false;
        }

        $payload = json_decode(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]', true) ?: [];
        $issuedAtIso = \iEducar\Packages\AdvancedReports\Services\DocumentSigningService::issuedAtForMac($doc->issued_at);
        if (class_exists(\iEducar\Packages\BuritiAssinaturaDigital\Services\ValidacaoPublicaService::class)) {
            $payload['validation_url'] = app(\iEducar\Packages\BuritiAssinaturaDigital\Services\ValidacaoPublicaService::class)
                ->urlPublica((string) $doc->code);
        }
        $novo = $signing->mac((string) $doc->code, (string) $doc->type, $issuedAtIso, $payload);
        if (! $signing->verify($novo, (string) $doc->code, (string) $doc->type, $issuedAtIso, $payload)) {
            return false;
        }

        try {
            $doc->payload = $payload;
            $doc->mac = $novo;
            $doc->save();
        } catch (Throwable) {
            return true;
        }

        return true;
    }

    /** QR data-URI para URL de validação (uso em PDF/HTML). */
    public function qrParaUrl(string $url): ?string
    {
        return $this->qrDataUri($url);
    }

    private function qrDataUri(string $url): ?string
    {
        if (class_exists(\iEducar\Packages\AdvancedReports\Services\QrCodeService::class)) {
            try {
                return app(\iEducar\Packages\AdvancedReports\Services\QrCodeService::class)->pngDataUri($url, 4);
            } catch (Throwable) {
                //
            }
        }

        // Fallback HTTP (HTML/impressão). PDF usa o link textual.
        return 'https://api.qrserver.com/v1/create-qr-code/?size=160x160&ecc=M&margin=4&data='.rawurlencode($url);
    }
}
