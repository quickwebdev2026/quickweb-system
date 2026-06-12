<?php

namespace Quickweb\System\Support;

class QuickwebValueResolvers
{
  /**
   * @param mixed $value
   */
  public static function resolveCountry($value): ?string
  {
    $label = SettingValue::resolveLabel($value);

    if ($label !== null && !is_numeric($label)) {
      return $label;
    }

    $id = SettingValue::resolveId($value);

    if ($id === null || $id === '') {
      return null;
    }

    $countryClass = 'Modules\\Ecom\\Entities\\EcomCountry';

    if (!class_exists($countryClass)) {
      return is_scalar($id) ? (string) $id : null;
    }

    try {
      $country = $countryClass::query()->with('lang')->find($id);

      if ($country && isset($country->lang->name)) {
        return (string) $country->lang->name;
      }
    } catch (\Throwable $e) {
      return is_scalar($id) ? (string) $id : null;
    }

    return is_scalar($id) ? (string) $id : null;
  }

  /**
   * @param mixed $value
   */
  public static function resolveState($value): ?string
  {
    $label = SettingValue::resolveLabel($value);

    if ($label !== null && !is_numeric($label)) {
      return $label;
    }

    $id = SettingValue::resolveId($value);

    if ($id === null || $id === '') {
      return null;
    }

    $stateClass = 'Modules\\Ecom\\Entities\\EcomState';

    if (!class_exists($stateClass)) {
      return is_scalar($id) ? (string) $id : null;
    }

    try {
      $state = $stateClass::query()->with('lang')->find($id);

      if ($state && isset($state->lang->name)) {
        return (string) $state->lang->name;
      }
    } catch (\Throwable $e) {
      return is_scalar($id) ? (string) $id : null;
    }

    return is_scalar($id) ? (string) $id : null;
  }
}
