<?php

namespace iEducar\Packages\BuritiExportacaoUnificada\Drivers;

use iEducar\Packages\BuritiExportacaoUnificada\Support\Formato;
use iEducar\Packages\BuritiExportacaoUnificada\Support\TabelaExportacao;

final class JpegDriver implements DriverContrato
{
    public function formato(): Formato
    {
        return Formato::Jpeg;
    }

    public function gerar(TabelaExportacao $tabela): string
    {
        if (!function_exists('imagecreatetruecolor')) {
            throw new \RuntimeException('Extensão GD necessária para JPEG.');
        }

        $linhas = array_merge(
            [$tabela->titulo],
            $tabela->subtitulo !== '' ? [$tabela->subtitulo, ''] : [''],
            [implode(' | ', $tabela->cabecalhos)],
            array_map(static fn (array $l): string => implode(' | ', $l), $tabela->matriz()),
        );

        $altura = max(240, 40 + count($linhas) * 22);
        $img = imagecreatetruecolor(900, $altura);
        if ($img === false) {
            throw new \RuntimeException('Falha ao criar imagem.');
        }

        $fundo = imagecolorallocate($img, 238, 243, 242);
        $tinta = imagecolorallocate($img, 3, 22, 27);
        $mint = imagecolorallocate($img, 14, 127, 108);
        imagefilledrectangle($img, 0, 0, 900, $altura, $fundo);
        imagestring($img, 5, 20, 12, $this->ascii('i-Educar exportacao · Laguna Onda 0'), $mint);

        $y = 40;
        foreach ($linhas as $linha) {
            imagestring($img, 3, 20, $y, $this->ascii(mb_strimwidth($linha, 0, 100, '...')), $tinta);
            $y += 22;
        }

        ob_start();
        imagejpeg($img, null, 90);
        imagedestroy($img);
        $bin = ob_get_clean();
        if ($bin === false || $bin === '') {
            throw new \RuntimeException('Falha ao gerar JPEG.');
        }

        return $bin;
    }

    private function ascii(string $texto): string
    {
        $map = [
            'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'é' => 'e', 'ê' => 'e',
            'í' => 'i', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ú' => 'u', 'ç' => 'c',
            'Á' => 'A', 'Ã' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Õ' => 'O', 'Ú' => 'U', 'Ç' => 'C',
        ];

        return strtr($texto, $map);
    }
}
