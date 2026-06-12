<?php

namespace Quickweb\System\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Quickweb\System\Contracts\StateStoreInterface;

class DiagnosticSender
{
  /** @var DiagnosticPayloadBuilder */
  private $payloadBuilder;

  /** @var StateStoreInterface */
  private $state;

  /** @var ScheduleEvaluator */
  private $evaluator;

  /** @var string */
  private $endpoint;

  /** @var int */
  private $timeout;

  /** @var int */
  private $retries;

  public function __construct(
    DiagnosticPayloadBuilder $payloadBuilder,
    StateStoreInterface $state,
    ScheduleEvaluator $evaluator,
    string $endpoint,
    int $timeout,
    int $retries
  ) {
    $this->payloadBuilder = $payloadBuilder;
    $this->state = $state;
    $this->evaluator = $evaluator;
    $this->endpoint = $endpoint;
    $this->timeout = $timeout;
    $this->retries = $retries;
  }

  public function send(bool $force = false): bool
  {
    $event = $force ? 'manual' : $this->evaluator->resolveEvent();

    if ($event === null) {
      return false;
    }

    $payload = $this->payloadBuilder->build($event);
    $attempts = max(1, $this->retries + 1);
    $success = false;

    for ($attempt = 1; $attempt <= $attempts; $attempt++) {
      try {
        $response = Http::timeout($this->timeout)
          ->acceptJson()
          ->asJson()
          ->post($this->endpoint, $payload);

        if ($response->successful()) {
          $success = true;
          break;
        }

        Log::warning('quickweb/system: endpoint returned non-success status', [
          'status' => $response->status(),
          'attempt' => $attempt,
        ]);
      } catch (\Throwable $e) {
        Log::warning('quickweb/system: failed to send diagnostics', [
          'message' => $e->getMessage(),
          'attempt' => $attempt,
        ]);
      }

      if ($attempt < $attempts) {
        usleep(250000);
      }
    }

    if ($success) {
      $this->persistState($event);
    }

    return $success;
  }

  private function persistState(string $event): void
  {
    $now = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format(DATE_ATOM);
    $existing = $this->state->get();

    $this->state->save(array_merge($existing, [
      'initialized_at' => $existing['initialized_at'] ?? $now,
      'last_sent_at' => $now,
      'last_event' => $event,
    ]));
  }
}
