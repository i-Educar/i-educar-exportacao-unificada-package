<?php

namespace iEducar\Packages\BuritiExportacaoUnificada\Drivers;

use iEducar\Packages\BuritiExportacaoUnificada\Support\Formato;
use iEducar\Packages\BuritiExportacaoUnificada\Support\TabelaExportacao;
use iEducar\Packages\BuritiExportacaoUnificada\Support\ZipEscritor;

final class DocxDriver implements DriverContrato
{
    public function formato(): Formato
    {
        return Formato::Docx;
    }

    public function gerar(TabelaExportacao $tabela): string
    {
        $paras = $this->p($tabela->titulo, true);
        if ($tabela->subtitulo !== '') {
            $paras .= $this->p($tabela->subtitulo, false);
        }
        $paras .= $this->p(implode(' | ', $tabela->cabecalhos), true);
        foreach ($tabela->matriz() as $linha) {
            $paras .= $this->p(implode(' | ', $linha), false);
        }

        $document = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            . '<w:body>' . $paras . '</w:body></w:document>';

        return ZipEscritor::montar([
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
                . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
                . '<Default Extension="xml" ContentType="application/xml"/>'
                . '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
                . '</Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
                . '</Relationships>',
            'word/document.xml' => $document,
        ]);
    }

    private function p(string $texto, bool $negrito): string
    {
        $t = htmlspecialchars($texto, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $rPr = $negrito ? '<w:rPr><w:b/></w:rPr>' : '';

        return '<w:p><w:r>' . $rPr . '<w:t xml:space="preserve">' . $t . '</w:t></w:r></w:p>';
    }
}
