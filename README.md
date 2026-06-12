# quickweb/system

Core system package for Quickweb-based Laravel applications.

This package is part of the standard platform stack and is expected to be present in production deployments. It handles routine system maintenance tasks and integrates with the host application configuration layer.

## Requirements

- PHP ^7.3|^8.0
- Laravel ^8.0|^9.0|^10.0|^11.0

## Installation

```bash
composer require quickweb/system
```

Publish configuration (recommended):

```bash
php artisan vendor:publish --tag=system-config
```

Review the published `config/system.php` file and set the corresponding `SYSTEM_*` environment variables for your environment.

## Scheduler

The package relies on the Laravel task scheduler. Add the following entry to the server crontab:

```cron
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## Host application

Applications that store settings outside of Laravel config should bind `Quickweb\System\Contracts\SettingResolverInterface` in a service provider. Optional value resolvers can be configured in `config/system.php` — see the published config for details.

## License

MIT
