<?php

namespace iEducar\Packages\BuritiExportacaoUnificada\Support;

/**
 * Metadados do layout institucional (cabeçalho, rodapé, logos, assinaturas, QR).
 * Usado por qualquer módulo Buriti ao emitir via EmissorDocumento.
 */
final class DocumentoInstitucional
{
    /**
     * @param  list<array{nome?:string,cargo:string,quando?:?string}>  $assinaturas
     */
    public function __construct(
        public readonly string $entidade,
        public readonly string $secretaria,
        public readonly string $sistema,
        public readonly string $titulo,
        public readonly string $subtitulo = '',
        public readonly string $moduloOrigem = '',
        public readonly string $logoEsquerda = '',
        public readonly string $logoDireita = '',
        public readonly string $rodapeExtra = '',
        public readonly array $assinaturas = [],
        public readonly bool $comValidacao = true,
        public readonly ?string $codigoValidacao = null,
        public readonly ?string $urlValidacao = null,
        public readonly ?string $qrDataUri = null,
        public readonly ?string $emitidoPor = null,
        public readonly ?string $emitidoEm = null,
    ) {
    }

    public static function fromConfig(
        string $titulo,
        string $subtitulo = '',
        string $moduloOrigem = '',
        ?string $emitidoPor = null,
        bool $comValidacao = true,
        ?array $assinaturas = null,
    ): self {
        $cfg = (array) config('exportacao.institucional', []);
        $cargos = $assinaturas;
        if ($cargos === null) {
            $cargos = [];
            if ((bool) ($cfg['mostrar_assinaturas'] ?? true)) {
                foreach ((array) ($cfg['cargos_assinatura'] ?? []) as $cargo) {
                    $cargos[] = ['cargo' => (string) $cargo, 'nome' => ''];
                }
            }
        }

        return new self(
            entidade: (string) ($cfg['entidade'] ?? 'Prefeitura Municipal'),
            secretaria: (string) ($cfg['secretaria'] ?? 'Secretaria Municipal de Educação'),
            sistema: (string) ($cfg['sistema'] ?? 'i-Educar'),
            titulo: $titulo,
            subtitulo: $subtitulo,
            moduloOrigem: $moduloOrigem,
            logoEsquerda: (string) ($cfg['logo_esquerda'] ?? ''),
            logoDireita: (string) ($cfg['logo_direita'] ?? ''),
            rodapeExtra: (string) ($cfg['rodape_extra'] ?? ''),
            assinaturas: $cargos,
            comValidacao: $comValidacao && (bool) config('exportacao.validacao.habilitado', true),
            emitidoPor: $emitidoPor,
            emitidoEm: now()->format('d/m/Y H:i:s'),
        );
    }

    public function comValidacaoEmitida(string $codigo, string $url, ?string $qrDataUri = null): self
    {
        return new self(
            entidade: $this->entidade,
            secretaria: $this->secretaria,
            sistema: $this->sistema,
            titulo: $this->titulo,
            subtitulo: $this->subtitulo,
            moduloOrigem: $this->moduloOrigem,
            logoEsquerda: $this->logoEsquerda,
            logoDireita: $this->logoDireita,
            rodapeExtra: $this->rodapeExtra,
            assinaturas: $this->assinaturas,
            comValidacao: true,
            codigoValidacao: $codigo,
            urlValidacao: $url,
            qrDataUri: $qrDataUri,
            emitidoPor: $this->emitidoPor,
            emitidoEm: $this->emitidoEm,
        );
    }
}
