<?php

namespace Quickweb\System\Services;

use Illuminate\Contracts\Foundation\Application;
use Quickweb\System\Contracts\DataCollectorInterface;

class DiagnosticPayloadBuilder
{
  /** @var Application */
  private $app;

  /** @var array<int, string> */
  private $collectorClasses;

  public function __construct(Application $app, array $collectorClasses)
  {
    $this->app = $app;
    $this->collectorClasses = $collectorClasses;
  }

  public function build(string $event): array
  {
    $payload = [
      'event' => $event,
      'sent_at' => (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format(DATE_ATOM),
      'site_id' => $this->buildSiteId(),
    ];

    foreach ($this->collectorClasses as $collectorClass) {
      /** @var DataCollectorInterface $collector */
      $collector = $this->app->make($collectorClass);
      $payload = array_merge($payload, $collector->collect());
    }

    return $payload;
  }

  private function buildSiteId(): string
  {
    $key = (string) config('app.key', env('APP_KEY', ''));

    if ($key === '') {
      return '';
    }

    return hash('sha256', $key);
  }
}
