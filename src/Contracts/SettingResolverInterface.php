<?php

namespace Quickweb\System\Contracts;

interface SettingResolverInterface
{
  /**
   * @param mixed $default
   * @return mixed
   */
  public function get(string $key, $default = null);
}
