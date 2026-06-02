<?php

$srcPath = __DIR__.'/../public/image/logo2.png';
$outPath = __DIR__.'/../public/image/logo2-transparent.png';

$src = imagecreatefrompng($srcPath);
if (!$src) {
    fwrite(STDERR, "Cannot load image: $srcPath\n");
    exit(1);
}

$width = imagesx($src);
$height = imagesy($src);

$dest = imagecreatetruecolor($width, $height);
imagealphablending($dest, false);
imagesavealpha($dest, true);
$transparent = imagecolorallocatealpha($dest, 0, 0, 0, 127);
imagefill($dest, 0, 0, $transparent);

for ($y = 0; $y < $height; $y++) {
    for ($x = 0; $x < $width; $x++) {
        $rgb = imagecolorat($src, $x, $y);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;

        $luminosity = ($r + $g + $b) / 3;

        if ($luminosity >= 140) {
            $alpha = (int) max(0, min(100, (200 - $luminosity) * 2));
            $white = imagecolorallocatealpha($dest, 255, 255, 255, $alpha);
            imagesetpixel($dest, $x, $y, $white);
        }
    }
}

imagepng($dest, $outPath, 9);
imagedestroy($src);
imagedestroy($dest);

echo "OK: $outPath\n";
