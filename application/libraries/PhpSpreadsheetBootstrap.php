<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Compatibility bridge for reports that were originally written with PHPExcel.
 *
 * PHPExcel cannot be parsed by PHP 8 because it uses the removed curly-brace
 * string-offset syntax. PhpSpreadsheet is its maintained successor. The
 * aliases keep the existing report code readable while all runtime classes
 * are supplied by PhpSpreadsheet.
 */
class PhpSpreadsheetBootstrap
{
    public static function load()
    {
        if (class_exists('PHPExcel', false)) {
            return;
        }

        $autoload = FCPATH . 'vendor/autoload.php';
        if (!is_file($autoload)) {
            show_error('Dependensi PhpSpreadsheet belum tersedia. Jalankan composer install --no-dev dengan PHP 8.3 sebelum memakai ekspor Excel.', 500);
            return;
        }

        require_once $autoload;

        $aliases = array(
            'PHPExcel' => '\\PhpOffice\\PhpSpreadsheet\\Spreadsheet',
            'PHPExcel_IOFactory' => '\\PhpOffice\\PhpSpreadsheet\\IOFactory',
            'PHPExcel_Style_Alignment' => '\\PhpOffice\\PhpSpreadsheet\\Style\\Alignment',
            'PHPExcel_Style_Border' => '\\PhpOffice\\PhpSpreadsheet\\Style\\Border',
            'PHPExcel_Worksheet_PageSetup' => '\\PhpOffice\\PhpSpreadsheet\\Worksheet\\PageSetup',
        );

        foreach ($aliases as $legacyClass => $modernClass) {
            if (!class_exists($legacyClass, false)) {
                class_alias($modernClass, $legacyClass);
            }
        }
    }
}
