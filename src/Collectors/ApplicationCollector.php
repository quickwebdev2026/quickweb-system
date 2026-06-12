<?php

namespace Quickweb\System\Collectors;

use Illuminate\Foundation\Application;
use Quickweb\System\Contracts\DataCollectorInterface;
use Quickweb\System\Support\PackageManifest;

class ApplicationCollector implements DataCollectorInterface
{
  /** @var Application */
  private $app;

  public function __construct(Application $app)
  {
    $this->app = $app;
  }

  public function collect(): array
  {
    $hostPackage = PackageManifest::forHostApplication();

    return [
      'application' => [
        'app_name' => (string) config('app.name', env('APP_NAME', '')),
        'app_env' => (string) config('app.env', env('APP_ENV', '')),
        'app_url' => (string) config('app.url', env('APP_URL', '')),
        'package_name' => $hostPackage['name'],
        'package_version' => $hostPackage['version'],
        'laravel_version' => Application::VERSION,
        'php_version' => PHP_VERSION,
        'debug_mode' => (bool) config('app.debug', false),
        'timezone' => (string) config('app.timezone', 'UTC'),
        'locale' => (string) config('app.locale', 'en'),
      ],
    ];
  }
}
