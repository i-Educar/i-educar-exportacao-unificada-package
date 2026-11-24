<?php

namespace iEducar\Packages\BuritiExportacaoUnificada\Drivers;

use iEducar\Packages\BuritiExportacaoUnificada\Support\Formato;
use iEducar\Packages\BuritiExportacaoUnificada\Support\TabelaExportacao;
use iEducar\Packages\BuritiExportacaoUnificada\Support\ZipEscritor;

final class XlsxDriver implements DriverContrato
{
    public function formato(): Formato
    {
        return Formato::Xlsx;
    }

    public function gerar(TabelaExportacao $tabela): string
    {
        $sheet = $this->sheetXml($tabela);

        return ZipEscritor::montar([
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
                . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
                . '<Default Extension="xml" ContentType="application/xml"/>'
                . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
                . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
                . '</Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
                . '</Relationships>',
            'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
                . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
                . '<sheets><sheet name="Exportacao" sheetId="1" r:id="rId1"/></sheets></workbook>',
            'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
                . '</Relationships>',
            'xl/worksheets/sheet1.xml' => $sheet,
        ]);
    }

    private function sheetXml(TabelaExportacao $tabela): string
    {
        $rows = '';
        $r = 1;
        $rows .= $this->rowXml($r++, $tabela->cabecalhos);
        foreach ($tabela->matriz() as $linha) {
            $rows .= $this->rowXml($r++, $linha);
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetData>' . $rows . '</sheetData></worksheet>';
    }

    /** @param list<string> $cells */
    private function rowXml(int $r, array $cells): string
    {
        $xml = '<row r="' . $r . '">';
        foreach ($cells as $i => $valor) {
            $col = $this->col($i) . $r;
            $xml .= '<c r="' . $col . '" t="inlineStr"><is><t>'
                . htmlspecialchars($valor, ENT_XML1 | ENT_QUOTES, 'UTF-8')
                . '</t></is></c>';
        }

        return $xml . '</row>';
    }

    private function col(int $index): string
    {
        $n = $index;
        $s = '';
        do {
            $s = chr(65 + ($n % 26)) . $s;
            $n = intdiv($n, 26) - 1;
        } while ($n >= 0);

        return $s;
    }
}
