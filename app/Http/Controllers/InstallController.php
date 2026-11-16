<?php

namespace App\Http\Controllers;

use App\Models\AgentToken;
use App\Support\Installer;
use Illuminate\Http\Request;

/**
 * One-time web installer for hosts without SSH (cPanel): open /install?key=<INSTALL_KEY from .env>.
 * Disabled once storage/app/installed.lock exists or when INSTALL_KEY is empty.
 */
class InstallController extends Controller
{
    protected function guard(Request $request): void
    {
        $key = (string) config('app.install_key');
        abort_if($key === '' || Installer::installed() || ! hash_equals($key, (string) $request->input('key')), 404);
    }

    public function show(Request $request)
    {
        $this->guard($request);

        return view('install', ['key' => $request->input('key')]);
    }

    public function store(Request $request)
    {
        $this->guard($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:90'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:10', 'confirmed'],
        ]);
        $log = Installer::run($data['name'], $data['email'], $data['password']);
        [, $plain] = AgentToken::issue('Claude');

        return view('install', ['done' => true, 'log' => $log, 'mcpUrl' => url('/mcp/'.$plain)]);
    }
}
