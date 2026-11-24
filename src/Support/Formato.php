<?php

namespace iEducar\Packages\BuritiExportacaoUnificada\Support;

enum Formato: string
{
    case Pdf = 'pdf';
    case Odt = 'odt';
    case Docx = 'docx';
    case Ods = 'ods';
    case Xlsx = 'xlsx';
    case Html = 'html';
    case Xml = 'xml';
    case Csv = 'csv';
    case Txt = 'txt';
    case Jpeg = 'jpeg';

    /** @return list<self> */
    public static function todos(): array
    {
        return self::cases();
    }

    /** Formatos mínimos do item 4.2 do TR. */
    /** @return list<self> */
    public static function relatorioTr(): array
    {
        return [
            self::Pdf, self::Odt, self::Docx, self::Ods, self::Xlsx,
            self::Html, self::Xml, self::Csv, self::Txt,
        ];
    }

    /** Formatos mínimos do item 4.3 do TR (listas/processos). */
    /** @return list<self> */
    public static function listaTr(): array
    {
        return [self::Pdf, self::Txt, self::Csv, self::Xlsx, self::Docx, self::Jpeg];
    }

    public function mime(): string
    {
        return match ($this) {
            self::Pdf => 'application/pdf',
            self::Odt => 'application/vnd.oasis.opendocument.text',
            self::Docx => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            self::Ods => 'application/vnd.oasis.opendocument.spreadsheet',
            self::Xlsx => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            self::Html => 'text/html; charset=UTF-8',
            self::Xml => 'application/xml; charset=UTF-8',
            self::Csv => 'text/csv; charset=UTF-8',
            self::Txt => 'text/plain; charset=UTF-8',
            self::Jpeg => 'image/jpeg',
        };
    }

    public function extensao(): string
    {
        return $this->value === 'jpeg' ? 'jpg' : $this->value;
    }
}
