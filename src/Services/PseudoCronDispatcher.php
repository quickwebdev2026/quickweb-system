<?php

namespace Quickweb\System\Services;

use Illuminate\Support\Facades\Log;
use Quickweb\System\Jobs\SendDiagnosticsJob;

class PseudoCronDispatcher
{
  /** @var string */
  private $url;

  /** @var int */
  private $timeout;

  public function __construct(string $url, int $timeout = 1)
  {
    $this->url = $url;
    $this->timeout = $timeout;
  }

  public function dispatch(): void
  {
    if (config('system.pseudo_cron.use_background_curl', true) && $this->dispatchBackgroundCurl()) {
      return;
    }

    $this->dispatchAfterResponse();
  }

  private function dispatchBackgroundCurl(): bool
  {
    if (!function_exists('exec')) {
      return false;
    }

    $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));

    if (in_array('exec', $disabled, true)) {
      return false;
    }

    $url = escapeshellarg($this->url);
    $command = "curl -s -X POST -o /dev/null -m {$this->timeout} {$url} > /dev/null 2>&1 &";

    try {
      exec($command);

      return true;
    } catch (\Throwable $e) {
      return false;
    }
  }

  private function dispatchAfterResponse(): void
  {
    if (config('queue.default') === 'sync') {
      SendDiagnosticsJob::dispatchSync();

      return;
    }

    try {
      SendDiagnosticsJob::dispatchAfterResponse();
    } catch (\Throwable $e) {
      SendDiagnosticsJob::dispatch();
    }
  }
}
