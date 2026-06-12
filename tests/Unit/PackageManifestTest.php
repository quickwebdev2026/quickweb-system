<?php

namespace Quickweb\System\Tests\Unit;

use Quickweb\System\Support\PackageManifest;
use Quickweb\System\Tests\TestCase;

class PackageManifestTest extends TestCase
{
  public function test_it_resolves_diagnostics_package_manifest(): void
  {
    $manifest = PackageManifest::forDiagnosticsPackage();

    $this->assertSame('quickweb/system', $manifest['name']);
    $this->assertNotSame('', $manifest['version']);
  }
}
