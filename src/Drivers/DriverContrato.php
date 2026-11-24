<?php

namespace iEducar\Packages\BuritiExportacaoUnificada\Drivers;

use iEducar\Packages\BuritiExportacaoUnificada\Support\Formato;
use iEducar\Packages\BuritiExportacaoUnificada\Support\TabelaExportacao;

interface DriverContrato
{
    public function formato(): Formato;

    public function gerar(TabelaExportacao $tabela): string;
}
