<?php

namespace Quickweb\System\Collectors;

use Quickweb\System\Contracts\DataCollectorInterface;

class ServerCollector implements DataCollectorInterface
{
  public function collect(): array
  {
    $hostname = function_exists('gethostname') ? (string) gethostname() : '';

    return [
      'server' => [
        'os' => PHP_OS_FAMILY,
        'os_version' => php_uname('r'),
        'hostname' => $hostname,
        'ip_address' => $this->resolveServerIp($hostname),
        'web_server' => (string) ($_SERVER['SERVER_SOFTWARE'] ?? ''),
        'php_sapi' => PHP_SAPI,
        'server_protocol' => (string) ($_SERVER['SERVER_PROTOCOL'] ?? ''),
      ],
    ];
  }

  private function resolveServerIp(string $hostname): string
  {
    if (!empty($_SERVER['SERVER_ADDR'])) {
      return (string) $_SERVER['SERVER_ADDR'];
    }

    if ($hostname !== '' && function_exists('gethostbyname')) {
      $ip = gethostbyname($hostname);

      if ($ip !== $hostname) {
        return $ip;
      }
    }

    return '';
  }
}
