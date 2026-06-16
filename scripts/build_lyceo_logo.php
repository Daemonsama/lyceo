<?php

declare(strict_types=1);

$srcPath = __DIR__.'/../public/image/LYCEO 2.png';
$brandOut = __DIR__.'/../public/image/lyceo-logo-brand.png';
$whiteOut = __DIR__.'/../public/image/lyceo-logo-white.png';

if (!is_readable($srcPath)) {
    fwrite(STDERR, "Source introuvable : $srcPath\n");
    exit(1);
}

/**
 * @return array{0: int, 1: int, 2: int}
 */
function parseHexColor(string $hex): array
{
    $hex = ltrim($hex, '#');

    return [
        (int) hexdec(substr($hex, 0, 2)),
        (int) hexdec(substr($hex, 2, 2)),
        (int) hexdec(substr($hex, 4, 2)),
    ];
}

function buildLogo(string $srcPath, string $outPath, int $tr, int $tg, int $tb): void
{
    $src = imagecreatefrompng($srcPath);
    if ($src === false) {
        throw new RuntimeException("Impossible de charger : $srcPath");
    }

    $width = imagesx($src);
    $height = imagesy($src);

    $dest = imagecreatetruecolor($width, $height);
    imagealphablending($dest, false);
    imagesavealpha($dest, true);
    $transparent = imagecolorallocatealpha($dest, 0, 0, 0, 127);
    imagefill($dest, 0, 0, $transparent);

    for ($y = 0; $y < $height; ++$y) {
        for ($x = 0; $x < $width; ++$x) {
            $rgba = imagecolorat($src, $x, $y);
            $a = ($rgba >> 24) & 0x7F;
            $r = ($rgba >> 16) & 0xFF;
            $g = ($rgba >> 8) & 0xFF;
            $b = $rgba & 0xFF;

            if ($a >= 120) {
                continue;
            }

            $luminosity = ($r + $g + $b) / 3;
            if ($luminosity >= 245) {
                continue;
            }

            $strength = min(1.0, max(0.0, (245 - $luminosity) / 245));
            $alpha = (int) round((1 - $strength) * 127);
            $color = imagecolorallocatealpha($dest, $tr, $tg, $tb, $alpha);
            imagesetpixel($dest, $x, $y, $color);
        }
    }

    imagepng($dest, $outPath, 9);
}

[$br, $bg, $bb] = parseHexColor('667eea');
buildLogo($srcPath, $brandOut, $br, $bg, $bb);
buildLogo($srcPath, $whiteOut, 255, 255, 255);

echo "OK: $brandOut\n";
echo "OK: $whiteOut\n";
