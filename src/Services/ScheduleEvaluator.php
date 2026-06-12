<?php

namespace Quickweb\System\Services;

use Quickweb\System\Contracts\StateStoreInterface;

class ScheduleEvaluator
{
  /** @var StateStoreInterface */
  private $state;

  /** @var int */
  private $interval;

  public function __construct(StateStoreInterface $state, int $interval)
  {
    $this->state = $state;
    $this->interval = $interval;
  }

  public function shouldSendInitial(): bool
  {
    return !$this->state->isInitialized();
  }

  public function shouldSendWeekly(): bool
  {
    if (!$this->state->isInitialized()) {
      return false;
    }

    $lastSentAt = $this->state->getLastSentAt();

    if ($lastSentAt === null) {
      return true;
    }

    $dueAt = $lastSentAt->getTimestamp() + $this->interval;

    return time() >= $dueAt;
  }

  public function isDue(): bool
  {
    return $this->shouldSendInitial() || $this->shouldSendWeekly();
  }

  public function resolveEvent(): ?string
  {
    if ($this->shouldSendInitial()) {
      return 'initial';
    }

    if ($this->shouldSendWeekly()) {
      return 'weekly';
    }

    return null;
  }
}
