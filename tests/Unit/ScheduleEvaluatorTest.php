<?php

namespace Quickweb\System\Tests\Unit;

use Quickweb\System\Contracts\StateStoreInterface;
use Quickweb\System\Services\ScheduleEvaluator;
use Quickweb\System\Support\FileStateStore;
use Illuminate\Filesystem\Filesystem;
use Quickweb\System\Tests\TestCase;

class ScheduleEvaluatorTest extends TestCase
{
  private function makeEvaluator(array $state, int $interval = 604800): ScheduleEvaluator
  {
    $path = storage_path('framework/testing-state-' . uniqid('', true) . '.json');
    $store = new FileStateStore(new Filesystem(), $path);

    if (!empty($state)) {
      $store->save($state);
    }

    return new ScheduleEvaluator($store, $interval);
  }

  public function test_initial_send_is_due_when_not_initialized(): void
  {
    $evaluator = $this->makeEvaluator([]);

    $this->assertTrue($evaluator->shouldSendInitial());
    $this->assertTrue($evaluator->isDue());
    $this->assertSame('initial', $evaluator->resolveEvent());
  }

  public function test_weekly_send_is_due_after_interval(): void
  {
    $evaluator = $this->makeEvaluator([
      'initialized_at' => '2020-01-01T00:00:00+00:00',
      'last_sent_at' => '2020-01-01T00:00:00+00:00',
      'last_event' => 'initial',
    ]);

    $this->assertFalse($evaluator->shouldSendInitial());
    $this->assertTrue($evaluator->shouldSendWeekly());
    $this->assertSame('weekly', $evaluator->resolveEvent());
  }
}
