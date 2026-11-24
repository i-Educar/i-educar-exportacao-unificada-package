<?php

namespace iEducar\Packages\BuritiExportacaoUnificada\Drivers;

use iEducar\Packages\BuritiExportacaoUnificada\Support\Formato;
use iEducar\Packages\BuritiExportacaoUnificada\Support\TabelaExportacao;

final class HtmlDriver implements DriverContrato
{
    public function formato(): Formato
    {
        return Formato::Html;
    }

    public function gerar(TabelaExportacao $tabela): string
    {
        $e = static fn (string $v): string => htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html = '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="utf-8"><title>'
            . $e($tabela->titulo) . '</title>'
            . '<style>body{font-family:sans-serif;margin:24px}table{border-collapse:collapse;width:100%}'
            . 'th,td{border:1px solid #ccc;padding:6px 8px;text-align:left}th{background:#eef6f4}</style>'
            . '</head><body><h1>' . $e($tabela->titulo) . '</h1>';
        if ($tabela->subtitulo !== '') {
            $html .= '<p>' . $e($tabela->subtitulo) . '</p>';
        }
        $html .= '<table><thead><tr>';
        foreach ($tabela->cabecalhos as $c) {
            $html .= '<th>' . $e($c) . '</th>';
        }
        $html .= '</tr></thead><tbody>';
        foreach ($tabela->matriz() as $linha) {
            $html .= '<tr>';
            foreach ($linha as $cel) {
                $html .= '<td>' . $e($cel) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table></body></html>';

        return $html;
    }
}
