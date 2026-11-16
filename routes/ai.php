<?php

use App\Http\Middleware\AuthenticateAgent;
use App\Mcp\Servers\MicroStudioServer;
use Laravel\Mcp\Facades\Mcp;

// Claude connector URL: https://<site>/mcp/<token>   (token from the admin: «اتصال کلاد»)
Mcp::web('/mcp/{token}', MicroStudioServer::class)->middleware([AuthenticateAgent::class, 'throttle:240,1'])->where('token', 'ms_[A-Za-z0-9]+');

// Same server for clients that send the token as "Authorization: Bearer <token>" (e.g. Claude Code)
Mcp::web('/mcp', MicroStudioServer::class)->middleware([AuthenticateAgent::class, 'throttle:240,1']);
