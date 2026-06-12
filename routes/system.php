<?php

use Illuminate\Support\Facades\Route;
use Quickweb\System\Http\Controllers\RunDiagnosticsController;

$prefix = trim((string) config('system.pseudo_cron.route_prefix', '_system'), '/');
$routeName = (string) config('system.pseudo_cron.route_name', 'system.diagnostics.run');

Route::post("{$prefix}/run/{token}", RunDiagnosticsController::class)
  ->name($routeName);
