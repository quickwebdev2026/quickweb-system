<?php

namespace Quickweb\System\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Quickweb\System\Jobs\SendDiagnosticsJob;
use Quickweb\System\Services\SystemManager;

class RunDiagnosticsController extends Controller
{
  public function __invoke(string $token, SystemManager $manager): Response
  {
    if (!$manager->isEnabled()) {
      return response('', Response::HTTP_NO_CONTENT);
    }

    if ($token === '' || !hash_equals($manager->resolveToken(), $token)) {
      return response('', Response::HTTP_FORBIDDEN);
    }

    $lock = Cache::lock('quickweb.system.send', 300);

    if (!$lock->get()) {
      return response('', Response::HTTP_NO_CONTENT);
    }

    try {
      if ($manager->isDue()) {
        SendDiagnosticsJob::dispatch();
      }
    } finally {
      optional($lock)->release();
    }

    return response('', Response::HTTP_NO_CONTENT);
  }
}
