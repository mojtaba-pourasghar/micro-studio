<?php

namespace App\Support;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use InvalidArgumentException;

/** Stores images under public/uploads/YYYY/MM and records them in the media library. */
class MediaStore
{
    public const MAX_BYTES = 10 * 1024 * 1024;

    public const MIMES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif', 'image/avif' => 'avif', 'video/mp4' => 'mp4'];

    public static function fromUpload(UploadedFile $file, ?string $alt = null, string $source = 'admin'): Media
    {
        return static::fromContents(file_get_contents($file->getRealPath()), $file->getClientOriginalName(), $alt, $source);
    }

    public static function fromBase64(string $data, string $filename, ?string $alt = null, string $source = 'agent'): Media
    {
        if (str_contains($data, ',') && str_starts_with($data, 'data:')) {
            $data = substr($data, strpos($data, ',') + 1);
        }
        $bytes = base64_decode($data, true);
        if ($bytes === false) {
            throw new InvalidArgumentException('Invalid base64 data.');
        }

        return static::fromContents($bytes, $filename, $alt, $source);
    }

    public static function fromUrl(string $url, ?string $filename = null, ?string $alt = null, string $source = 'agent'): Media
    {
        if (! preg_match('~^https://~i', $url)) {
            throw new InvalidArgumentException('Only https:// URLs are allowed.');
        }
        $res = Http::timeout(30)->withOptions(['stream' => false])->get($url);
        if (! $res->successful()) {
            throw new InvalidArgumentException('Download failed with HTTP '.$res->status().'.');
        }
        $filename ??= basename(parse_url($url, PHP_URL_PATH) ?: 'image') ?: 'image';

        return static::fromContents($res->body(), $filename, $alt, $source);
    }

    public static function fromContents(string $bytes, string $filename, ?string $alt = null, string $source = 'admin'): Media
    {
        $size = strlen($bytes);
        if ($size === 0 || $size > self::MAX_BYTES) {
            throw new InvalidArgumentException('File must be between 1 byte and 10 MB.');
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($bytes) ?: '';
        if (! isset(self::MIMES[$mime])) {
            throw new InvalidArgumentException("Unsupported file type: $mime (allowed: jpg, png, webp, gif, avif, mp4).");
        }
        $base = Str::slug(pathinfo($filename, PATHINFO_FILENAME)) ?: 'image';
        $dir = 'uploads/'.now()->format('Y/m');
        $path = $dir.'/'.Str::limit($base, 60, '').'-'.Str::lower(Str::random(6)).'.'.self::MIMES[$mime];
        if (! is_dir(public_path($dir))) {
            mkdir(public_path($dir), 0755, true);
        }
        file_put_contents(public_path($path), $bytes);
        $dims = str_starts_with($mime, 'image/') ? @getimagesizefromstring($bytes) : false;

        return Media::create([
            'path' => $path, 'original_name' => Str::limit($filename, 190, ''), 'alt' => $alt, 'mime' => $mime, 'size' => $size,
            'width' => $dims[0] ?? null, 'height' => $dims[1] ?? null, 'source' => $source,
        ]);
    }
}
