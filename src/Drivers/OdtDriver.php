<?php

namespace iEducar\Packages\BuritiExportacaoUnificada\Drivers;

use iEducar\Packages\BuritiExportacaoUnificada\Support\Formato;
use iEducar\Packages\BuritiExportacaoUnificada\Support\TabelaExportacao;
use iEducar\Packages\BuritiExportacaoUnificada\Support\ZipEscritor;

final class OdtDriver implements DriverContrato
{
    public function formato(): Formato
    {
        return Formato::Odt;
    }

    public function gerar(TabelaExportacao $tabela): string
    {
        $body = '<text:h text:style-name="Heading_20_1" text:outline-level="1">'
            . $this->esc($tabela->titulo) . '</text:h>';
        if ($tabela->subtitulo !== '') {
            $body .= '<text:p>' . $this->esc($tabela->subtitulo) . '</text:p>';
        }
        $body .= '<table:table table:name="Exportacao"><table:table-column table:number-columns-repeated="'
            . count($tabela->cabecalhos) . '"/>';
        $body .= '<table:table-row>';
        foreach ($tabela->cabecalhos as $c) {
            $body .= '<table:table-cell office:value-type="string"><text:p>' . $this->esc($c) . '</text:p></table:table-cell>';
        }
        $body .= '</table:table-row>';
        foreach ($tabela->matriz() as $linha) {
            $body .= '<table:table-row>';
            foreach ($linha as $cel) {
                $body .= '<table:table-cell office:value-type="string"><text:p>' . $this->esc($cel) . '</text:p></table:table-cell>';
            }
            $body .= '</table:table-row>';
        }
        $body .= '</table:table>';

        $content = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<office:document-content xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" '
            . 'xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" '
            . 'xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" office:version="1.2">'
            . '<office:body><office:text>' . $body . '</office:text></office:body></office:document-content>';

        return ZipEscritor::montar([
            'mimetype' => 'application/vnd.oasis.opendocument.text',
            'META-INF/manifest.xml' => '<?xml version="1.0" encoding="UTF-8"?>'
                . '<manifest:manifest xmlns:manifest="urn:oasis:names:tc:opendocument:xmlns:manifest:1.0">'
                . '<manifest:file-entry manifest:full-path="/" manifest:media-type="application/vnd.oasis.opendocument.text"/>'
                . '<manifest:file-entry manifest:full-path="content.xml" manifest:media-type="text/xml"/>'
                . '</manifest:manifest>',
            'content.xml' => $content,
        ]);
    }

    private function esc(string $v): string
    {
        return htmlspecialchars($v, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
