<?php

namespace Quickweb\System\Support;

use Quickweb\System\Contracts\SettingResolverInterface;

class NullSettingResolver implements SettingResolverInterface
{
  public function get(string $key, $default = null)
  {
    return $default;
  }
}
