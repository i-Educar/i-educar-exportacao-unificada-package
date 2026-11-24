<?php

namespace iEducar\Packages\BuritiExportacaoUnificada\Drivers;

use iEducar\Packages\BuritiExportacaoUnificada\Support\Formato;
use iEducar\Packages\BuritiExportacaoUnificada\Support\TabelaExportacao;

final class XmlDriver implements DriverContrato
{
    public function formato(): Formato
    {
        return Formato::Xml;
    }

    public function gerar(TabelaExportacao $tabela): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><exportacao/>');
        $xml->addChild('titulo', $this->esc($tabela->titulo));
        if ($tabela->subtitulo !== '') {
            $xml->addChild('subtitulo', $this->esc($tabela->subtitulo));
        }
        $cols = $xml->addChild('colunas');
        foreach ($tabela->cabecalhos as $c) {
            $cols->addChild('coluna', $this->esc($c));
        }
        $rows = $xml->addChild('linhas');
        foreach ($tabela->matriz() as $linha) {
            $row = $rows->addChild('linha');
            foreach ($tabela->cabecalhos as $i => $nome) {
                $cel = $row->addChild('celula', $this->esc($linha[$i] ?? ''));
                $cel->addAttribute('coluna', $nome);
            }
        }

        $dom = dom_import_simplexml($xml)->ownerDocument;
        $dom->formatOutput = true;

        return $dom->saveXML() ?: $xml->asXML() ?: '';
    }

    private function esc(string $v): string
    {
        return htmlspecialchars($v, ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
