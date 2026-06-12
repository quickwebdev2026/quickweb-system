<?php

namespace Quickweb\System\Support;

use Quickweb\System\Contracts\SettingResolverInterface;

class HostSettingResolver implements SettingResolverInterface
{
  public function get(string $key, $default = null)
  {
    if (function_exists('get_value_setting')) {
      return get_value_setting($key, $default);
    }

    $settingClass = 'App\\Models\\Setting';

    if (class_exists($settingClass) && method_exists($settingClass, 'getSetting')) {
      try {
        $setting = $settingClass::getSetting($key);

        if ($setting && isset($setting->value)) {
          return $setting->value;
        }
      } catch (\Throwable $e) {
        // Host application settings are optional.
      }
    }

    return $default;
  }
}
