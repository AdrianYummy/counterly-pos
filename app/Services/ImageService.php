<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ImageService
{
    public function store(UploadedFile $file, string $directory, int $maxWidth = 1200, int $maxHeight = 1200): string
    {
        $info = @getimagesize($file->getRealPath());
        if (! $info || ! in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            throw new RuntimeException('The uploaded file is not a supported image.');
        }

        $source = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($file->getRealPath()),
            IMAGETYPE_PNG => @imagecreatefrompng($file->getRealPath()),
            IMAGETYPE_WEBP => @imagecreatefromwebp($file->getRealPath()),
        };
        if (! $source) {
            throw new RuntimeException('The uploaded image could not be read.');
        }

        $scale = min(1, $maxWidth / $info[0], $maxHeight / $info[1]);
        $width = max(1, (int) round($info[0] * $scale));
        $height = max(1, (int) round($info[1] * $scale));
        $canvas = imagecreatetruecolor($width, $height);
        $background = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $background);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $width, $height, $info[0], $info[1]);

        ob_start();
        $encoded = imagewebp($canvas, null, 82);
        $contents = ob_get_clean();
        imagedestroy($source);
        imagedestroy($canvas);

        if (! $encoded || $contents === false) {
            throw new RuntimeException('The uploaded image could not be prepared.');
        }

        $path = trim($directory, '/').'/'.Str::uuid().'.webp';
        Storage::disk('public')->put($path, $contents);

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path && (str_starts_with($path, 'products/') || str_starts_with($path, 'avatars/'))) {
            Storage::disk('public')->delete($path);
        }
    }
}
