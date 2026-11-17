<?php

namespace App;

/** Encodes booking codes as ISO QR version 1, byte mode, error correction L. */
class QrCode
{
    public function svg(string $value): string
    {
        if (strlen($value) > 17) {
            throw new \InvalidArgumentException('Booking code exceeds QR capacity.');
        }
        $bits = '0100'.sprintf('%08b', strlen($value));
        foreach (str_split($value) as $byte) {
            $bits .= sprintf('%08b', ord($byte));
        }
        $bits .= '0000';
        $bits = str_pad($bits, (int) (ceil(strlen($bits) / 8) * 8), '0');
        $data = array_map(static fn (string $byte): int => intval($byte, 2), str_split($bits, 8));
        for ($i = count($data); $i < 19; $i++) {
            $data[] = ($i - (int) (strlen($bits) / 8)) % 2 === 0 ? 236 : 17;
        }
        $generator = [1];
        $root = 1;
        for ($i = 0; $i < 7; $i++) {
            $next = array_fill(0, count($generator) + 1, 0);
            foreach ($generator as $index => $coefficient) {
                $next[$index] ^= $coefficient;
                $next[$index + 1] ^= $this->multiply($coefficient, $root);
            }
            $generator = $next;
            $root = $this->multiply($root, 2);
        }
        $remainder = array_merge($data, array_fill(0, 7, 0));
        for ($i = 0; $i < 19; $i++) {
            $factor = $remainder[$i];
            foreach ($generator as $j => $coefficient) {
                $remainder[$i + $j] ^= $this->multiply($coefficient, $factor);
            }
        }
        $stream = '';
        foreach (array_merge($data, array_slice($remainder, 19)) as $byte) {
            $stream .= sprintf('%08b', $byte);
        }
        $matrix = array_fill(0, 21, array_fill(0, 21, -1));
        foreach ([[0, 0], [14, 0], [0, 14]] as [$left, $top]) {
            for ($y = -1; $y <= 7; $y++) {
                for ($x = -1; $x <= 7; $x++) {
                    if ($left + $x < 0 || $left + $x >= 21 || $top + $y < 0 || $top + $y >= 21) {
                        continue;
                    }
                    $matrix[$top + $y][$left + $x] = (int) ($x >= 0 && $x <= 6 && $y >= 0 && $y <= 6 && ($x === 0 || $x === 6 || $y === 0 || $y === 6 || ($x >= 2 && $x <= 4 && $y >= 2 && $y <= 4)));
                }
            }
        }
        for ($i = 8; $i < 13; $i++) {
            $matrix[6][$i] = $matrix[$i][6] = (int) ($i % 2 === 0);
        }
        for ($i = 0; $i < 15; $i++) {
            $bit = (0x77C4 >> $i) & 1;
            $vertical = $i < 6 ? $i : ($i < 8 ? $i + 1 : 6 + $i);
            $horizontal = $i < 8 ? 20 - $i : ($i === 8 ? 7 : 14 - $i);
            $matrix[$vertical][8] = $bit;
            $matrix[8][$horizontal] = $bit;
        }
        $matrix[13][8] = 1;
        $index = 0;
        $up = true;
        for ($right = 20; $right >= 1; $right -= 2) {
            if ($right === 6) {
                $right = 5;
            }
            for ($offset = 0; $offset < 21; $offset++) {
                $y = $up ? 20 - $offset : $offset;
                for ($column = 0; $column < 2; $column++) {
                    $x = $right - $column;
                    if ($matrix[$y][$x] !== -1) {
                        continue;
                    }
                    $bit = (int) ($stream[$index++] ?? '0');
                    $matrix[$y][$x] = $bit ^ (int) (($x + $y) % 2 === 0);
                }
            }
            $up = ! $up;
        }
        $path = '';
        foreach ($matrix as $y => $row) {
            foreach ($row as $x => $bit) {
                if ($bit) {
                    $path .= 'M'.($x + 4).' '.($y + 4).'h1v1h-1z';
                }
            }
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 29 29" shape-rendering="crispEdges"><rect width="29" height="29" fill="white"/><path d="'.$path.'" fill="black"/></svg>';
    }

    private function multiply(int $left, int $right): int
    {
        $result = 0;
        while ($right > 0) {
            if ($right & 1) {
                $result ^= $left;
            }
            $left <<= 1;
            if ($left & 256) {
                $left ^= 285;
            }
            $right >>= 1;
        }

        return $result;
    }
}
