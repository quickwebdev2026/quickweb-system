<?php

namespace Quickweb\System\Support;

class PackageManifest
{
  public const DIAGNOSTICS_PACKAGE = 'quickweb/system';

  /**
   * @return array{name: string, version: string}
   */
  public static function forDiagnosticsPackage(): array
  {
    return [
      'name' => self::DIAGNOSTICS_PACKAGE,
      'version' => self::resolveInstalledVersion(self::DIAGNOSTICS_PACKAGE, self::packageComposerPath()),
    ];
  }

  /**
   * @return array{name: string, version: string}
   */
  public static function forHostApplication(): array
  {
    $path = function_exists('base_path') ? base_path('composer.json') : '';

    if ($path === '' || !is_readable($path)) {
      return ['name' => '', 'version' => ''];
    }

    $data = self::readComposerJson($path);

    return [
      'name' => (string) ($data['name'] ?? ''),
      'version' => self::resolveInstalledVersion((string) ($data['name'] ?? ''), $path),
    ];
  }

  private static function packageComposerPath(): string
  {
    return dirname(__DIR__, 2) . '/composer.json';
  }

  /**
   * @return array<string, mixed>
   */
  private static function readComposerJson(string $path): array
  {
    $contents = file_get_contents($path);
    $data = json_decode($contents ?: '', true);

    return is_array($data) ? $data : [];
  }

  private static function resolveInstalledVersion(string $packageName, string $composerJsonPath): string
  {
    if ($packageName !== '' && class_exists(\Composer\InstalledVersions::class)) {
      if (\Composer\InstalledVersions::isInstalled($packageName)) {
        $version = \Composer\InstalledVersions::getPrettyVersion($packageName);

        if ($version !== null && $version !== '') {
          return $version;
        }
      }
    }

    if (is_readable($composerJsonPath)) {
      $version = self::readComposerJson($composerJsonPath)['version'] ?? null;

      if (is_string($version) && $version !== '') {
        return $version;
      }
    }

    return 'dev';
  }
}
