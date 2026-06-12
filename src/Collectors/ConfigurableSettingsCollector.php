<?php

namespace Quickweb\System\Collectors;

use Quickweb\System\Contracts\DataCollectorInterface;
use Quickweb\System\Contracts\SettingResolverInterface;
use Quickweb\System\Contracts\ValueResolverInterface;

class ConfigurableSettingsCollector implements DataCollectorInterface
{
  /** @var SettingResolverInterface */
  private $settings;

  /** @var array<string, string|null> */
  private $settingsMap;

  /** @var array<string, mixed> */
  private $valueResolvers;

  /**
   * @param array<string, string|null> $settingsMap
   * @param array<string, mixed> $valueResolvers
   */
  public function __construct(
    SettingResolverInterface $settings,
    array $settingsMap,
    array $valueResolvers = []
  ) {
    $this->settings = $settings;
    $this->settingsMap = $settingsMap;
    $this->valueResolvers = $valueResolvers;
  }

  public function collect(): array
  {
    $administration = [];
    $store = [];

    foreach ($this->settingsMap as $dotPath => $settingKey) {
      if ($settingKey === null || $settingKey === '') {
        continue;
      }

      $value = $this->settings->get($settingKey);
      $this->assignNested($dotPath, $value, $administration, $store);
    }

    $store = $this->resolveStoreLabels($store);

    $payload = [];

    if (!empty($administration)) {
      $payload['administration'] = $administration;
    }

    if (!empty($store)) {
      $payload['store'] = $store;
    }

    return $payload;
  }

  /**
   * @param mixed $value
   */
  private function assignNested(
    string $dotPath,
    $value,
    array &$administration,
    array &$store
  ): void {
    $segments = explode('.', $dotPath, 2);

    if (count($segments) !== 2) {
      return;
    }

    [$group, $field] = $segments;

    if ($group === 'administration') {
      $administration[$field] = $value;
    } elseif ($group === 'store') {
      $store[$field] = $value;
    }
  }

  private function resolveStoreLabels(array $store): array
  {
    if (isset($store['country_id'])) {
      $store['country'] = $this->resolveValue('country_id', $store['country_id']);
      unset($store['country_id']);
    }

    if (isset($store['state_id'])) {
      $store['state'] = $this->resolveValue('state_id', $store['state_id']);
      unset($store['state_id']);
    }

    return $store;
  }

  /**
   * @param mixed $value
   */
  private function resolveValue(string $resolverKey, $value): ?string
  {
    if ($value === null || $value === '') {
      return null;
    }

    $resolver = $this->valueResolvers[$resolverKey] ?? null;

    if ($resolver === null) {
      return is_scalar($value) ? (string) $value : null;
    }

    if (is_callable($resolver)) {
      $resolved = $resolver($value);

      return $resolved === null ? null : (string) $resolved;
    }

    if (is_string($resolver) && class_exists($resolver)) {
      $instance = app($resolver);

      if ($instance instanceof ValueResolverInterface) {
        return $instance->resolve($value);
      }
    }

    return is_scalar($value) ? (string) $value : null;
  }
}
