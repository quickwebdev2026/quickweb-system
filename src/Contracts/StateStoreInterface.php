<?php

namespace Quickweb\System\Contracts;

interface StateStoreInterface
{
  public function get(): array;

  public function save(array $state): void;

  public function isInitialized(): bool;

  public function getLastSentAt(): ?\DateTimeInterface;
}
