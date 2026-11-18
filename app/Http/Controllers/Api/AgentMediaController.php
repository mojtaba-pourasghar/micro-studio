<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Actor;
use App\Support\MediaStore;
use Illuminate\Http\Request;

/** REST upload for large images (e.g. Canva exports) from a cloud session: curl -F file=@x.png -H "Authorization: Bearer ms_…" */
class AgentMediaController extends Controller
{
    public function ping()
    {
        return response()->json(['ok' => true, 'token' => Actor::agent()?->name, 'site' => url('/')]);
    }

    public function store(Request $request)
    {
        abort_unless(Actor::agent()?->allows('media.upload'), 403, 'This token lacks the media.upload ability.');
        $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimetypes:'.implode(',', array_keys(MediaStore::MIMES))],
            'alt' => ['nullable', 'string', 'max:190'],
            'source' => ['nullable', 'in:agent,canva'],
            'watermark' => ['nullable', 'boolean'],
        ]);
        $media = MediaStore::fromUpload($request->file('file'), $request->input('alt'), $request->input('source', 'agent'), $request->boolean('watermark', true));

        return response()->json(['ok' => true, 'id' => $media->id, 'path' => $media->path, 'url' => $media->url(), 'width' => $media->width, 'height' => $media->height], 201);
    }
}
