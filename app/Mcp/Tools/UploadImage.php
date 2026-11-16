<?php

namespace App\Mcp\Tools;

use App\Support\MediaStore;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Upload an image (jpg, png, webp, gif, avif; max 10 MB) to the media library and get its "path" to put in image fields. Give either base64 (raw or data: URI) or an https url. The site server is in Iran and may not reach foreign URLs (e.g. Canva exports): if a url fails, download the file yourself and send base64, or use the REST endpoint POST /api/agent/media (multipart "file", same token as Bearer).')]
class UploadImage extends AgentTool
{
    public function handle(Request $request): Response
    {
        if ($denied = $this->deny('media.upload')) {
            return $denied;
        }
        $filename = (string) ($request->get('filename') ?: 'image');
        $alt = $request->get('alt');
        $source = $request->get('source') === 'canva' ? 'canva' : 'agent';
        try {
            if ($b64 = $request->get('base64')) {
                $media = MediaStore::fromBase64((string) $b64, $filename, $alt, $source);
            } elseif ($url = $request->get('url')) {
                $media = MediaStore::fromUrl((string) $url, $request->get('filename'), $alt, $source);
            } else {
                return Response::error('Give "base64" or "url".');
            }
        } catch (\Throwable $e) {
            return Response::error('Upload failed: '.$e->getMessage());
        }

        return $this->json(['ok' => true, 'id' => $media->id, 'path' => $media->path, 'url' => $media->url(), 'width' => $media->width, 'height' => $media->height]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'filename' => $schema->string()->description('Descriptive latin file name, e.g. "razino-chapter-11-cover.webp".')->required(),
            'alt' => $schema->string()->description('Persian alternative text describing the image.')->required(),
            'base64' => $schema->string()->description('Image bytes as base64 or a data: URI.'),
            'url' => $schema->string()->description('https URL to download from (may be unreachable from Iran).'),
            'source' => $schema->string()->enum(['agent', 'canva'])->description('Where the image was made.'),
        ];
    }
}
