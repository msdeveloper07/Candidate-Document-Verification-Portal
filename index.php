<?php

/*
 * Front controller for the flattened layout: this file, .htaccess and
 * assets/ have been lifted out of public/ so the URL carries no /public.
 * Paths therefore point at the current directory rather than one above.
 */

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/vendor/autoload.php';

(require_once __DIR__.'/bootstrap/app.php')->handleRequest(Request::capture());
