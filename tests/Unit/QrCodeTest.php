<?php

use App\QrCode;

test('booking QR matches a golden vector independently decoded by OpenCV', function () {
    $svg = (new QrCode)->svg('KS123456789012');

    expect(hash('sha256', $svg))->toBe('f6b791ffa0957b1a5e514b29cfc84b1e6bbf7a83c10307e609adf2b3100c140d');
});

test('QR rejects values exceeding its supported capacity', function () {
    expect(fn () => (new QrCode)->svg(str_repeat('A', 18)))->toThrow(InvalidArgumentException::class);
});
