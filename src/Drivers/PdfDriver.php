<?php

namespace iEducar\Packages\BuritiExportacaoUnificada\Drivers;

use iEducar\Packages\BuritiExportacaoUnificada\Support\Formato;
use iEducar\Packages\BuritiExportacaoUnificada\Support\TabelaExportacao;

/** PDF mínimo (ISO 32000) com texto tabular — sem dependência externa. */
final class PdfDriver implements DriverContrato
{
    public function formato(): Formato
    {
        return Formato::Pdf;
    }

    public function gerar(TabelaExportacao $tabela): string
    {
        $linhas = [$this->win($tabela->titulo)];
        if ($tabela->subtitulo !== '') {
            $linhas[] = $this->win($tabela->subtitulo);
        }
        $linhas[] = '';
        $linhas[] = $this->win(implode(' | ', $tabela->cabecalhos));
        $linhas[] = str_repeat('-', 72);
        foreach ($tabela->matriz() as $linha) {
            $linhas[] = $this->win(implode(' | ', $linha));
        }

        $y = 800;
        $ops = ["BT /F1 11 Tf 40 {$y} Td"];
        foreach ($linhas as $i => $linha) {
            $safe = $this->pdfString($linha);
            if ($i === 0) {
                $ops[] = "({$safe}) Tj";
            } else {
                $ops[] = "0 -14 Td ({$safe}) Tj";
            }
        }
        $ops[] = 'ET';
        $stream = implode("\n", $ops);

        $objs = [];
        $objs[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objs[2] = '<< /Type /Pages /Kids [3 0 R] /Count 1 >>';
        $objs[3] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>';
        $objs[4] = '<< /Length ' . strlen($stream) . " >>\nstream\n{$stream}\nendstream";
        $objs[5] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objs as $n => $body) {
            $offsets[$n] = strlen($pdf);
            $pdf .= "{$n} 0 obj\n{$body}\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= 'xref' . "\n0 " . (count($objs) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($objs); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= "trailer\n<< /Size " . (count($objs) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xref}\n%%EOF\n";

        return $pdf;
    }

    private function win(string $texto): string
    {
        $map = [
            'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
            'é' => 'e', 'ê' => 'e', 'è' => 'e',
            'í' => 'i', 'ì' => 'i',
            'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ú' => 'u', 'ü' => 'u',
            'ç' => 'c', 'ñ' => 'n',
            'Á' => 'A', 'À' => 'A', 'Ã' => 'A', 'Â' => 'A',
            'É' => 'E', 'Ê' => 'E',
            'Í' => 'I',
            'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O',
            'Ú' => 'U',
            'Ç' => 'C',
        ];

        return strtr($texto, $map);
    }

    private function pdfString(string $texto): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $texto);
    }
}
