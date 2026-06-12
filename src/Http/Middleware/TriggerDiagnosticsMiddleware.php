<?php

namespace Quickweb\System\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Quickweb\System\Services\PseudoCronDispatcher;
use Quickweb\System\Services\SystemManager;

class TriggerDiagnosticsMiddleware
{
  /** @var SystemManager */
  private $manager;

  /** @var PseudoCronDispatcher */
  private $dispatcher;

  public function __construct(SystemManager $manager, PseudoCronDispatcher $dispatcher)
  {
    $this->manager = $manager;
    $this->dispatcher = $dispatcher;
  }

  public function handle(Request $request, Closure $next)
  {
    $response = $next($request);

    if (!$this->shouldAttemptTrigger($request)) {
      return $response;
    }

    if ($this->manager->isDue()) {
      $this->dispatcher->dispatch();
    }

    return $response;
  }

  private function shouldAttemptTrigger(Request $request): bool
  {
    if (!$this->manager->isEnabled()) {
      return false;
    }

    if (!config('system.pseudo_cron.enabled', true)) {
      return false;
    }

    $routeName = config('system.pseudo_cron.route_name', 'system.diagnostics.run');

    if ($request->route() && $request->route()->getName() === $routeName) {
      return false;
    }

    $sampleRate = max(1, (int) config('system.pseudo_cron.sample_rate', 50));

    return random_int(1, $sampleRate) === 1;
  }
}
