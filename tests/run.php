<?php

declare(strict_types=1);

/**
 * Testes de regra sem banco — Onda 0 Laguna (exportação unificada).
 * Uso: php tests/run.php
 */

$root = dirname(__DIR__);
$src = $root . '/src';

$files = [
    'Support/Formato.php',
    'Support/TabelaExportacao.php',
    'Support/ArquivoExportado.php',
    'Support/ZipEscritor.php',
    'Drivers/DriverContrato.php',
    'Drivers/CsvDriver.php',
    'Drivers/TxtDriver.php',
    'Drivers/HtmlDriver.php',
    'Drivers/XmlDriver.php',
    'Drivers/PdfDriver.php',
    'Drivers/JpegDriver.php',
    'Drivers/XlsxDriver.php',
    'Drivers/DocxDriver.php',
    'Drivers/OdtDriver.php',
    'Drivers/OdsDriver.php',
    'Services/Exportador.php',
];

foreach ($files as $file) {
    require_once $src . '/' . $file;
}

use iEducar\Packages\BuritiExportacaoUnificada\Services\Exportador;
use iEducar\Packages\BuritiExportacaoUnificada\Support\Formato;
use iEducar\Packages\BuritiExportacaoUnificada\Support\TabelaExportacao;

$falhas = 0;
$ok = 0;

$assert_true = static function (bool $cond, string $msg) use (&$falhas, &$ok): void {
    if ($cond) {
        echo "  OK  {$msg}\n";
        $ok++;
    } else {
        echo "  FAIL {$msg}\n";
        $falhas++;
    }
};

echo "== Exportação unificada — testes Onda 0 ==\n";

$exportador = new Exportador();
$disponiveis = $exportador->formatosDisponiveis();
$assert_true(count($disponiveis) === 10, '10 formatos registrados');

foreach (Formato::todos() as $formato) {
    $assert_true(in_array($formato->value, $disponiveis, true), "driver {$formato->value} presente");
}

$tabela = Exportador::amostraOnda0();
$assert_true(count($tabela->cabecalhos) === 4, 'amostra com 4 colunas');
$assert_true(count($tabela->linhas) === 3, 'amostra com 3 linhas');

$dir = $root . '/storage/test-onda0';
if (is_dir($dir)) {
    foreach (glob($dir . '/*') ?: [] as $f) {
        @unlink($f);
    }
}

$gerados = [];
foreach (Formato::todos() as $formato) {
    $arquivo = $exportador->exportar($tabela, $formato, 'prova-onda0-laguna');
    $assert_true($arquivo->tamanho() > 0, "{$formato->value} gera bytes");
    $caminho = $arquivo->gravar($dir);
    $assert_true(is_file($caminho), "{$formato->value} gravado em disco");
    $gerados[$formato->value] = $caminho;
}

// Assinaturas mínimas
$assert_true(str_starts_with(file_get_contents($gerados['pdf']) ?: '', '%PDF'), 'PDF começa com %PDF');
$assert_true(str_contains(file_get_contents($gerados['csv']) ?: '', 'Escola'), 'CSV contém cabeçalho');
$assert_true(str_contains(file_get_contents($gerados['html']) ?: '', '<table'), 'HTML contém table');
$assert_true(str_contains(file_get_contents($gerados['xml']) ?: '', '<exportacao'), 'XML raiz exportacao');
$assert_true(str_starts_with(file_get_contents($gerados['jpeg']) ?: '', "\xFF\xD8\xFF"), 'JPEG SOI');

foreach (['xlsx', 'docx', 'odt', 'ods'] as $zipFmt) {
    $bin = file_get_contents($gerados[$zipFmt]) ?: '';
    $assert_true(str_starts_with($bin, 'PK'), "{$zipFmt} é ZIP/OOXML/ODF");
}

// Gate Onda 0: ≥ 3 formatos
$assert_true(count($gerados) >= 3, 'Gate Onda 0: ≥ 3 formatos gerados');

// TR 4.2 conjunto
foreach (Formato::relatorioTr() as $formato) {
    $assert_true(isset($gerados[$formato->value]), "TR 4.2 cobre {$formato->value}");
}
// TR 4.3 conjunto
foreach (Formato::listaTr() as $formato) {
    $assert_true(isset($gerados[$formato->value]), "TR 4.3 cobre {$formato->value}");
}

// Validação tabela vazia de linhas ainda exporta
$vazia = new TabelaExportacao('Vazia', ['A'], [], 'sem linhas');
$csvVazio = $exportador->exportar($vazia, Formato::Csv);
$assert_true(str_contains($csvVazio->conteudo, 'A'), 'CSV só com cabeçalho');

try {
    new TabelaExportacao('x', [], []);
    $assert_true(false, 'tabela sem cabeçalho deve falhar');
} catch (InvalidArgumentException) {
    $assert_true(true, 'tabela sem cabeçalho lança InvalidArgumentException');
}

echo "\nResultado: {$ok} ok, {$falhas} falhas\n";
exit($falhas > 0 ? 1 : 0);
