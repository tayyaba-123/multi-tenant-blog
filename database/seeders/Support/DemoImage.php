<?php

namespace Database\Seeders\Support;

use GdImage;
use Illuminate\Http\UploadedFile;

/**
 * Generates simple branded PNGs with GD so demo data needs no binary assets.
 */
class DemoImage
{
    public static function logo(string $hex): UploadedFile
    {
        $size = 256;
        $image = imagecreatetruecolor($size, $size);
        [$r, $g, $b] = self::rgb($hex);

        imagefill($image, 0, 0, imagecolorallocate($image, $r, $g, $b));
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefilledellipse($image, 128, 128, 150, 150, $white);
        imagefilledellipse($image, 128, 128, 80, 80, imagecolorallocate($image, $r, $g, $b));

        return self::toUpload($image, 'logo.png');
    }

    public static function cover(string $hex, int $seed): UploadedFile
    {
        [$width, $height] = [1200, 600];
        $image = imagecreatetruecolor($width, $height);
        [$r, $g, $b] = self::rgb($hex);

        // Diagonal-ish gradient from the brand color to a light tint.
        for ($x = 0; $x < $width; $x++) {
            $t = $x / $width;
            $color = imagecolorallocate(
                $image,
                (int) ($r + (255 - $r) * $t * 0.7),
                (int) ($g + (255 - $g) * $t * 0.7),
                (int) ($b + (255 - $b) * $t * 0.7),
            );
            imageline($image, $x, 0, $x, $height, $color);
        }

        mt_srand($seed);
        for ($i = 0; $i < 6; $i++) {
            $alpha = imagecolorallocatealpha($image, 255, 255, 255, mt_rand(80, 110));
            $diameter = mt_rand(120, 420);
            imagefilledellipse($image, mt_rand(0, $width), mt_rand(0, $height), $diameter, $diameter, $alpha);
        }

        return self::toUpload($image, "cover-{$seed}.png");
    }

    /**
     * @return array{int, int, int}
     */
    private static function rgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }

    private static function toUpload(GdImage $image, string $name): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'demo').'.png';
        imagepng($image, $path);

        return new UploadedFile($path, $name, 'image/png', null, true);
    }
}
