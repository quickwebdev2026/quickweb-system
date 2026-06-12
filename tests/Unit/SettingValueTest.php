<?php

namespace Quickweb\System\Tests\Unit;

use Quickweb\System\Support\SettingValue;
use Quickweb\System\Tests\TestCase;

class SettingValueTest extends TestCase
{
  public function test_it_unwraps_async_select_values(): void
  {
    $this->assertSame(3, SettingValue::unwrapScalar(['value' => 3, 'label' => 'Hà Nội']));
    $this->assertSame('admin@test.com', SettingValue::unwrapScalar('admin@test.com'));
  }

  public function test_it_resolves_label_from_async_select(): void
  {
    $this->assertSame('Việt Nam', SettingValue::resolveLabel(['value' => 1, 'label' => 'Việt Nam']));
  }
}
