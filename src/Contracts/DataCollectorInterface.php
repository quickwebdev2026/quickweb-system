<?php

namespace Quickweb\System\Contracts;

interface DataCollectorInterface
{
  /**
   * @return array<string, mixed>
   */
  public function collect(): array;
}
