<?php

namespace iEducar\Packages\BuritiExportacaoUnificada\Support;

use iEducar\Packages\BuritiExportacaoUnificada\Services\ValidadorDocumentoBridge;

/**
 * Metadados do layout institucional (cabeçalho, rodapé, logos, assinaturas, QR).
 * Usado por qualquer módulo Buriti ao emitir via EmissorDocumento.
 */
final class DocumentoInstitucional
{
    /**
     * @param  list<array{
     *   nome?:string,
     *   cargo:string,
     *   quando?:?string,
     *   account?:string,
     *   email?:string,
     *   codigo_validacao?:?string,
     *   url_validacao?:?string,
     *   status?:?string
     * }>  $assinaturas
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
        public readonly ?int $pedidoAssinaturaId = null,
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
        $brand = self::brandingDoIeducar();
        $cargos = $assinaturas;
        if ($cargos === null) {
            $cargos = [];
            if ((bool) ($cfg['mostrar_assinaturas'] ?? true)) {
                foreach ((array) ($cfg['cargos_assinatura'] ?? []) as $cargo) {
                    $cargos[] = ['cargo' => (string) $cargo, 'nome' => ''];
                }
            }
        }

        $entidadeCfg = trim((string) ($cfg['entidade'] ?? ''));
        $secretariaCfg = trim((string) ($cfg['secretaria'] ?? ''));

        return new self(
            entidade: $entidadeCfg !== '' && ! self::parecePlaceholderApp($entidadeCfg)
                ? $entidadeCfg
                : $brand['prefeitura'],
            secretaria: $secretariaCfg !== ''
                ? $secretariaCfg
                : $brand['secretaria'],
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

    /**
     * Prefeitura / SME / endereço / telefone a partir da instituição do i-Educar.
     *
     * @return array{prefeitura:string,secretaria:string,contato:string,cidade:?string,uf:?string}
     */
    public static function brandingDoIeducar(): array
    {
        $cidade = null;
        $uf = null;
        $nmInst = '';
        $nmResp = '';
        $contatoParts = [];

        try {
            if (class_exists(\App\Models\LegacyInstitution::class)) {
                /** @var \App\Models\LegacyInstitution|null $inst */
                $inst = \App\Models\LegacyInstitution::query()->orderBy('cod_instituicao')->first();
                if ($inst !== null) {
                    $nmInst = trim((string) ($inst->nm_instituicao ?? ''));
                    $nmResp = trim((string) ($inst->nm_responsavel ?? ''));
                    $cidade = trim((string) ($inst->cidade ?? '')) ?: null;
                    $uf = trim((string) ($inst->ref_sigla_uf ?? '')) ?: null;

                    $logradouro = trim((string) ($inst->logradouro ?? ''));
                    $endereco = trim((string) ($inst->address ?? ''));
                    if ($logradouro !== '') {
                        $contatoParts[] = $endereco !== '' ? $endereco : $logradouro;
                    } elseif ($endereco !== '' && ! str_starts_with($endereco, ', ')) {
                        $contatoParts[] = $endereco;
                    }

                    $fone = trim((string) ($inst->telefone ?? ''));
                    $ddd = trim((string) ($inst->ddd_telefone ?? ''));
                    if ($fone !== '') {
                        $contatoParts[] = $ddd !== '' ? '('.$ddd.') '.$fone : $fone;
                    }
                }
            }
        } catch (\Throwable) {
            //
        }

        $entityCfg = trim((string) (
            config('legacy.config.ieducar_entity_name')
            ?? config('legacy.app.entity.name')
            ?? ''
        ));

        // Prefeitura: preferir nm_instituicao quando já for "Prefeitura…"; senão montar pela cidade.
        if ($nmInst !== '' && preg_match('/^prefeitura\b/ui', $nmInst)) {
            $prefeitura = $nmInst;
        } elseif ($cidade !== null && $cidade !== '') {
            $prefeitura = 'Prefeitura Municipal de '.$cidade;
        } elseif ($nmInst !== '' && ! self::pareceNomeProduto($nmInst)) {
            $prefeitura = $nmInst;
        } else {
            $prefeitura = 'Prefeitura Municipal';
        }

        // SME: responsável institucional, senão config de entidade (se for secretaria), senão fallback com cidade.
        if ($nmResp !== '' && preg_match('/secretaria|educa/ui', $nmResp)) {
            $secretaria = $nmResp;
        } elseif ($entityCfg !== '' && ! self::pareceNomeProduto($entityCfg) && ! preg_match('/^prefeitura\b/ui', $entityCfg)) {
            $secretaria = $entityCfg;
        } elseif ($cidade !== null && $cidade !== '') {
            $secretaria = 'Secretaria Municipal de Educação de '.$cidade;
        } else {
            $secretaria = 'Secretaria Municipal de Educação';
        }

        return [
            'prefeitura' => $prefeitura,
            'secretaria' => $secretaria,
            'contato' => implode(' · ', array_values(array_filter($contatoParts))),
            'cidade' => $cidade,
            'uf' => $uf,
        ];
    }

    private static function pareceNomeProduto(string $nome): bool
    {
        $n = mb_strtolower(trim($nome));

        return $n === '' || $n === 'i-educar' || str_starts_with($n, 'i-educar');
    }

    private static function parecePlaceholderApp(string $nome): bool
    {
        $n = mb_strtolower(trim($nome));

        return $n === ''
            || self::pareceNomeProduto($n)
            || $n === 'laravel'
            || str_contains($n, 'ecossistema sge');
    }

    public function comValidacaoEmitida(string $codigo, string $url, ?string $qrDataUri = null): self
    {
        // Código/URL do documento ficam só no rodapé/QR — nunca sob cada assinatura.
        return $this->clonar(
            comValidacao: true,
            codigoValidacao: $codigo,
            urlValidacao: $url,
            qrDataUri: $qrDataUri,
        );
    }

    /**
     * Gera código próprio (diferente do documento) para cada signatário nomeado.
     */
    public function comCodigosAssinaturasIndividuais(ValidadorDocumentoBridge|null $bridge = null): self
    {
        if ($this->assinaturas === []) {
            return $this;
        }

        $bridge ??= app(ValidadorDocumentoBridge::class);
        $out = [];
        foreach ($this->assinaturas as $a) {
            $row = $a;
            $nome = trim((string) ($row['nome'] ?? $row['displayName'] ?? ''));
            $jaTem = trim((string) ($row['codigo_validacao'] ?? '')) !== '';
            if ($nome !== '' && ! $jaTem) {
                $reg = $bridge->registrar('assinatura_digital_signatario', [
                    'titulo' => $this->titulo.' — '.(string) ($row['cargo'] ?? $nome),
                    'modulo' => $this->moduloOrigem,
                    'cargo' => (string) ($row['cargo'] ?? ''),
                    'nome' => $nome,
                    'documento_codigo' => $this->codigoValidacao,
                ]);
                if (($reg['codigo'] ?? '') !== '') {
                    $row['codigo_validacao'] = (string) $reg['codigo'];
                }
            }
            $out[] = $row;
        }

        return $this->clonar(assinaturas: $out);
    }

    /**
     * @param  list<array<string,mixed>>  $assinaturas
     */
    public function comAssinaturas(array $assinaturas, ?int $pedidoAssinaturaId = null): self
    {
        return $this->clonar(
            assinaturas: $assinaturas,
            pedidoAssinaturaId: $pedidoAssinaturaId ?? $this->pedidoAssinaturaId,
        );
    }

    /**
     * @param  list<array<string,mixed>>|null  $assinaturas
     */
    private function clonar(
        ?array $assinaturas = null,
        ?bool $comValidacao = null,
        ?string $codigoValidacao = null,
        ?string $urlValidacao = null,
        ?string $qrDataUri = null,
        ?int $pedidoAssinaturaId = null,
        bool $clearPedido = false,
    ): self {
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
            assinaturas: $assinaturas ?? $this->assinaturas,
            comValidacao: $comValidacao ?? $this->comValidacao,
            codigoValidacao: $codigoValidacao ?? $this->codigoValidacao,
            urlValidacao: $urlValidacao ?? $this->urlValidacao,
            qrDataUri: $qrDataUri ?? $this->qrDataUri,
            emitidoPor: $this->emitidoPor,
            emitidoEm: $this->emitidoEm,
            pedidoAssinaturaId: $clearPedido ? null : ($pedidoAssinaturaId ?? $this->pedidoAssinaturaId),
        );
    }
}
