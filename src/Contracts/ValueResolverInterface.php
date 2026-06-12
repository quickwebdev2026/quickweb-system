<?php

namespace Quickweb\System\Contracts;

interface ValueResolverInterface
{
  public function resolve($value): ?string;
}
