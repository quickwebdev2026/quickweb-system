<?php

namespace Quickweb\System\Tests\Unit;

use Quickweb\System\Collectors\ConfigurableSettingsCollector;
use Quickweb\System\Contracts\SettingResolverInterface;
use Quickweb\System\Tests\TestCase;

class ConfigurableSettingsCollectorTest extends TestCase
{
  public function test_it_collects_administration_and_store_values(): void
  {
    $resolver = new class implements SettingResolverInterface {
      public function get(string $key, $default = null)
      {
        $values = [
          'admin_email' => 'admin@example.com',
          'ecom_store_country_id' => ['value' => 1, 'label' => 'Việt Nam'],
          'ecom_store_state_id' => ['value' => 2, 'label' => 'Hà Nội'],
          'ecom_store_city' => 'Cầu giấy',
          'ecom_store_address_1' => '11 Phong đình cảng',
          'ecom_store_address_zip_code' => '10000',
        ];

        return $values[$key] ?? $default;
      }
    };

    $collector = new ConfigurableSettingsCollector(
      $resolver,
      config('system.settings_map'),
      config('system.value_resolvers')
    );

    $payload = $collector->collect();

    $this->assertSame('admin@example.com', $payload['administration']['email']);
    $this->assertSame('Việt Nam', $payload['store']['country']);
    $this->assertSame('Hà Nội', $payload['store']['state']);
    $this->assertSame('Cầu giấy', $payload['store']['district']);
    $this->assertArrayNotHasKey('city', $payload['store']);
    $this->assertSame('11 Phong đình cảng', $payload['store']['address_line_1']);
    $this->assertSame('10000', $payload['store']['zipcode']);
  }
}
