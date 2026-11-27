<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;

class MediaController extends Controller
{
    public function __invoke(string $path): Response
    {
        abort_unless(
            preg_match('/\A(?:products|avatars)\/[A-Za-z0-9._-]+\z/', $path) === 1,
            404,
        );

        $disk = Storage::disk('public');

        abort_unless($disk->exists($path), 404);

        return response($disk->get($path), 200, [
            'Cache-Control' => 'private, max-age=86400',
            'Content-Type' => $disk->mimeType($path) ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
