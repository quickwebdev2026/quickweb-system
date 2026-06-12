<?php

namespace Quickweb\System\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Quickweb\System\Services\DiagnosticSender;

class SendDiagnosticsJob implements ShouldQueue
{
  use Dispatchable;
  use InteractsWithQueue;
  use Queueable;
  use SerializesModels;

  /** @var bool */
  public $force;

  public function __construct(bool $force = false)
  {
    $this->force = $force;
  }

  public function handle(DiagnosticSender $sender): void
  {
    $lock = Cache::lock('quickweb.system.send', 300);

    if (!$lock->get()) {
      return;
    }

    try {
      $sender->send($this->force);
    } finally {
      optional($lock)->release();
    }
  }
}
