<?php

namespace Quickweb\System\Collectors;

use Quickweb\System\Contracts\DataCollectorInterface;
use Quickweb\System\Support\PackageManifest;

class PackageCollector implements DataCollectorInterface
{
  public function collect(): array
  {
    $manifest = PackageManifest::forDiagnosticsPackage();

    return [
      'package' => [
        'name' => $manifest['name'],
        'version' => $manifest['version'],
      ],
    ];
  }
}
