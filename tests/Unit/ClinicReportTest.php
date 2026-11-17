<?php

use App\ClinicReport;

test('spreadsheet contains typed numbers and inert escaped text', function () {
    $content = (new ClinicReport)->spreadsheet([['Pasien', 'Total'], ['=HYPERLINK("bad") <script>&', 100000]]);
    $temporary = tempnam(sys_get_temp_dir(), 'clinic-test');
    file_put_contents($temporary, $content);

    try {
        $zip = new ZipArchive;
        expect($zip->open($temporary))->toBeTrue();
        $xml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $document = new DOMDocument;
        expect($document->loadXML($xml))->toBeTrue();
        expect($xml)->toContain('t="inlineStr"', '&lt;script&gt;&amp;', '<v>100000</v>')->not->toContain('<f>');
        expect($zip->locateName('[Content_Types].xml'))->not->toBeFalse();
        $zip->close();
    } finally {
        unlink($temporary);
    }
});

test('PDF paginates and its cross reference offsets point to valid objects', function () {
    $pdf = (new ClinicReport)->pdf(array_fill(0, 90, 'Patient (test) / consultation'));

    expect($pdf)->toStartWith('%PDF-1.4')->toContain('/Count 3', 'Patient \\(test\\)')->toEndWith('%%EOF');
    preg_match('/xref\n0 (\d+)\n(.*?)trailer/s', $pdf, $matches);
    $entries = explode("\n", trim($matches[2]));
    for ($id = 1; $id < count($entries); $id++) {
        $offset = (int) substr($entries[$id], 0, 10);
        expect(substr($pdf, $offset, strlen($id.' 0 obj')))->toBe($id.' 0 obj');
    }
});
