<?php

namespace Quickweb\System\Support;

class SettingValue
{
  /**
   * @param mixed $value
   * @return mixed
   */
  public static function unwrapScalar($value)
  {
    if ($value === null || $value === '') {
      return null;
    }

    if (is_scalar($value)) {
      return $value;
    }

    if (!is_array($value)) {
      return null;
    }

    if (array_key_exists('value', $value)) {
      return $value['value'];
    }

    if (array_key_exists('label', $value)) {
      return $value['label'];
    }

    return null;
  }

  /**
   * @param mixed $value
   */
  public static function resolveLabel($value): ?string
  {
    if ($value === null || $value === '') {
      return null;
    }

    if (is_scalar($value)) {
      return (string) $value;
    }

    if (!is_array($value)) {
      return null;
    }

    if (!empty($value['label'])) {
      return (string) $value['label'];
    }

    $unwrapped = self::unwrapScalar($value);

    return $unwrapped === null ? null : (string) $unwrapped;
  }

  /**
   * @param mixed $value
   */
  public static function resolveId($value)
  {
    if ($value === null || $value === '') {
      return null;
    }

    if (is_numeric($value)) {
      return $value;
    }

    if (is_array($value) && array_key_exists('value', $value)) {
      return $value['value'];
    }

    return $value;
  }
}
