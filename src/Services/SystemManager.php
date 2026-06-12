<?php

namespace Quickweb\System\Services;

use Illuminate\Contracts\Foundation\Application;

class SystemManager
{
  /** @var Application */
  private $app;

  /** @var ScheduleEvaluator */
  private $evaluator;

  public function __construct(Application $app, ScheduleEvaluator $evaluator)
  {
    $this->app = $app;
    $this->evaluator = $evaluator;
  }

  public function isEnabled(): bool
  {
    if (!config('system.enabled', false)) {
      return false;
    }

    $except = config('system.except_environments', []);

    if (in_array($this->app->environment(), $except, true)) {
      return false;
    }

    $endpoint = (string) config('system.endpoint', '');

    return $endpoint !== '';
  }

  public function isDue(): bool
  {
    return $this->evaluator->isDue();
  }

  public function resolveToken(): string
  {
    $token = config('system.token');

    if (!empty($token)) {
      return (string) $token;
    }

    $appKey = (string) config('app.key', env('APP_KEY', ''));

    if ($appKey === '') {
      return '';
    }

    return hash('sha256', $appKey . 'quickweb-system');
  }

  public function buildRunUrl(): string
  {
    $token = $this->resolveToken();

    try {
      return route(config('system.pseudo_cron.route_name', 'system.diagnostics.run'), [
        'token' => $token,
      ]);
    } catch (\Throwable $e) {
      $prefix = trim((string) config('system.pseudo_cron.route_prefix', '_system'), '/');

      return url("/{$prefix}/run/{$token}");
    }
  }
}
