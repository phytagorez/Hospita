<?php

namespace App;

use Illuminate\Support\Str;
use ZipArchive;

class ClinicReport
{
    /** @param list<list<string|int>> $rows */
    public function spreadsheet(array $rows): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';
        foreach ($rows as $index => $row) {
            $xml .= '<row r="'.($index + 1).'">';
            foreach ($row as $column => $value) {
                $label = '';
                $position = $column;
                do {
                    $label = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'[$position % 26].$label;
                    $position = intdiv($position, 26) - 1;
                } while ($position >= 0);
                $cell = $label.($index + 1);
                $xml .= is_int($value) ? '<c r="'.$cell.'"><v>'.$value.'</v></c>' : '<c r="'.$cell.'" t="inlineStr"><is><t>'.htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</t></is></c>';
            }
            $xml .= '</row>';
        }
        $xml .= '</sheetData></worksheet>';
        $temporary = tempnam(sys_get_temp_dir(), 'clinic');
        if ($temporary === false) {
            throw new \RuntimeException('Unable to create temporary spreadsheet.');
        }
        try {
            $zip = new ZipArchive;
            if ($zip->open($temporary, ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('Unable to create spreadsheet.');
            }
            $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>');
            $zip->addFromString('_rels/.rels', '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
            $zip->addFromString('xl/workbook.xml', '<?xml version="1.0"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Kunjungan Klinik" sheetId="1" r:id="rId1"/></sheets></workbook>');
            $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>');
            $zip->addFromString('xl/worksheets/sheet1.xml', $xml);
            $zip->close();

            $content = file_get_contents($temporary);
            if ($content === false) {
                throw new \RuntimeException('Unable to read spreadsheet.');
            }

            return $content;
        } finally {
            @unlink($temporary);
        }
    }

    /** @param list<string> $lines */
    public function pdf(array $lines): string
    {
        $wrapped = [];
        foreach ($lines as $line) {
            array_push($wrapped, ...explode("\n", wordwrap(Str::ascii($line), 85, "\n", true)));
        }
        $chunks = array_chunk($wrapped ?: ['Tidak ada data.'], 43);
        $objects = [1 => '<< /Type /Catalog /Pages 2 0 R >>', 3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>'];
        $kids = [];
        foreach ($chunks as $index => $page) {
            $pageId = 4 + $index * 2;
            $contentId = $pageId + 1;
            $kids[] = $pageId.' 0 R';
            $commands = "BT /F1 10 Tf 14 TL 40 800 Td\n";
            foreach ($page as $line) {
                $safe = str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', '', ' '], Str::ascii($line));
                $commands .= '('.$safe.") Tj T*\n";
            }
            $commands .= 'ET';
            $objects[$pageId] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 3 0 R >> >> /Contents '.$contentId.' 0 R >>';
            $objects[$contentId] = '<< /Length '.strlen($commands)." >>\nstream\n".$commands."\nendstream";
        }
        $objects[2] = '<< /Type /Pages /Kids ['.implode(' ', $kids).'] /Count '.count($kids).' >>';
        ksort($objects);
        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $id => $object) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id." 0 obj\n".$object."\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
        for ($id = 1; $id <= count($objects); $id++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$id]);
        }

        return $pdf."trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n".$xref."\n%%EOF";
    }
}
