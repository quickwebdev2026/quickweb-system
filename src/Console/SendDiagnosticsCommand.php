<?php

namespace Quickweb\System\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Quickweb\System\Services\DiagnosticSender;
use Quickweb\System\Services\SystemManager;

class SendDiagnosticsCommand extends Command
{
  protected $signature = 'system:send-diagnostics {--force : Send regardless of schedule}';

  protected $description = 'Send site diagnostic data to the configured endpoint';

  public function handle(DiagnosticSender $sender, SystemManager $manager): int
  {
    if (!$manager->isEnabled()) {
      $this->warn('System diagnostics is disabled.');

      return self::SUCCESS;
    }

    $force = (bool) $this->option('force');

    if (!$force && !$manager->isDue()) {
      $this->info('Diagnostics are not due yet.');

      return self::SUCCESS;
    }

    $lock = Cache::lock('quickweb.system.send', 300);

    if (!$lock->get()) {
      $this->info('Another diagnostics run is already in progress.');

      return self::SUCCESS;
    }

    try {
      $result = $sender->send($force);

      if ($result) {
        $this->info('Diagnostics sent successfully.');
      } else {
        $this->error('Failed to send diagnostics.');
      }

      return $result ? self::SUCCESS : self::FAILURE;
    } finally {
      optional($lock)->release();
    }
  }
}
