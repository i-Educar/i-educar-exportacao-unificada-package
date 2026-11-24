<?php

namespace iEducar\Packages\BuritiExportacaoUnificada\Services;

use iEducar\Packages\BuritiExportacaoUnificada\Drivers\CsvDriver;
use iEducar\Packages\BuritiExportacaoUnificada\Drivers\DocxDriver;
use iEducar\Packages\BuritiExportacaoUnificada\Drivers\DriverContrato;
use iEducar\Packages\BuritiExportacaoUnificada\Drivers\HtmlDriver;
use iEducar\Packages\BuritiExportacaoUnificada\Drivers\JpegDriver;
use iEducar\Packages\BuritiExportacaoUnificada\Drivers\OdsDriver;
use iEducar\Packages\BuritiExportacaoUnificada\Drivers\OdtDriver;
use iEducar\Packages\BuritiExportacaoUnificada\Drivers\PdfDriver;
use iEducar\Packages\BuritiExportacaoUnificada\Drivers\TxtDriver;
use iEducar\Packages\BuritiExportacaoUnificada\Drivers\XlsxDriver;
use iEducar\Packages\BuritiExportacaoUnificada\Drivers\XmlDriver;
use iEducar\Packages\BuritiExportacaoUnificada\Support\ArquivoExportado;
use iEducar\Packages\BuritiExportacaoUnificada\Support\Formato;
use iEducar\Packages\BuritiExportacaoUnificada\Support\TabelaExportacao;

final class Exportador
{
    /** @var array<string, DriverContrato> */
    private array $drivers = [];

    public function __construct(?iterable $drivers = null)
    {
        $lista = $drivers ?? [
            new CsvDriver(),
            new TxtDriver(),
            new HtmlDriver(),
            new XmlDriver(),
            new PdfDriver(),
            new JpegDriver(),
            new XlsxDriver(),
            new DocxDriver(),
            new OdtDriver(),
            new OdsDriver(),
        ];

        foreach ($lista as $driver) {
            $this->drivers[$driver->formato()->value] = $driver;
        }
    }

    /** @return list<string> */
    public function formatosDisponiveis(): array
    {
        return array_keys($this->drivers);
    }

    public function exportar(TabelaExportacao $tabela, Formato|string $formato, ?string $nomeBase = null): ArquivoExportado
    {
        $fmt = $formato instanceof Formato ? $formato : Formato::from(strtolower($formato));
        $driver = $this->drivers[$fmt->value] ?? null;
        if ($driver === null) {
            throw new \InvalidArgumentException("Formato não suportado: {$fmt->value}");
        }

        return new ArquivoExportado(
            $driver->gerar($tabela),
            $fmt,
            $nomeBase ?? $tabela->titulo,
        );
    }

    /**
     * Gera o mesmo relatório em vários formatos (gate Onda 0: ≥ 3).
     *
     * @param list<Formato|string>|null $formatos
     * @return list<ArquivoExportado>
     */
    public function exportarVarios(TabelaExportacao $tabela, ?array $formatos = null, ?string $nomeBase = null): array
    {
        $formatos ??= Formato::todos();
        $saida = [];
        foreach ($formatos as $formato) {
            $saida[] = $this->exportar($tabela, $formato, $nomeBase);
        }

        return $saida;
    }

    public static function amostraOnda0(): TabelaExportacao
    {
        return new TabelaExportacao(
            titulo: 'Prova Onda 0 — Exportação Laguna',
            cabecalhos: ['Escola', 'Turno', 'Alunos', 'Situacao'],
            linhas: [
                ['EMEF Centro', 'Matutino', 120, 'Ativa'],
                ['EMEI Praia', 'Vespertino', 48, 'Ativa'],
                ['EMEF Progresso', 'Integral', 86, 'Ativa'],
            ],
            subtitulo: 'TR 4.2 / 4.3 · i-Educar 2.12 · buriti/exportacao-unificada',
        );
    }
}
