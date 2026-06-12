<?php

namespace Quickweb\System;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Quickweb\System\Collectors\ConfigurableSettingsCollector;
use Quickweb\System\Console\SendDiagnosticsCommand;
use Quickweb\System\Contracts\SettingResolverInterface;
use Quickweb\System\Contracts\StateStoreInterface;
use Quickweb\System\Http\Middleware\TriggerDiagnosticsMiddleware;
use Quickweb\System\Services\DiagnosticPayloadBuilder;
use Quickweb\System\Services\DiagnosticSender;
use Quickweb\System\Services\PseudoCronDispatcher;
use Quickweb\System\Services\ScheduleEvaluator;
use Quickweb\System\Services\SystemManager;
use Quickweb\System\Support\FileStateStore;
use Quickweb\System\Support\NullSettingResolver;

class SystemServiceProvider extends ServiceProvider
{
  public function register(): void
  {
    $this->mergeConfigFrom(__DIR__ . '/../config/system.php', 'system');

    $this->app->singleton(StateStoreInterface::class, function ($app) {
      $path = storage_path('app/' . ltrim(config('system.state.path', 'system/state.json'), '/'));

      return new FileStateStore($app->make(Filesystem::class), $path);
    });

    $this->app->singleton(SettingResolverInterface::class, function () {
      return new NullSettingResolver();
    });

    $this->app->singleton(ScheduleEvaluator::class, function ($app) {
      return new ScheduleEvaluator(
        $app->make(StateStoreInterface::class),
        (int) config('system.interval', 604800)
      );
    });

    $this->app->singleton(DiagnosticPayloadBuilder::class, function ($app) {
      return new DiagnosticPayloadBuilder(
        $app,
        config('system.collectors', [])
      );
    });

    $this->app->singleton(ConfigurableSettingsCollector::class, function ($app) {
      return new ConfigurableSettingsCollector(
        $app->make(SettingResolverInterface::class),
        config('system.settings_map', []),
        config('system.value_resolvers', [])
      );
    });

    $this->app->singleton(DiagnosticSender::class, function ($app) {
      return new DiagnosticSender(
        $app->make(DiagnosticPayloadBuilder::class),
        $app->make(StateStoreInterface::class),
        $app->make(ScheduleEvaluator::class),
        (string) config('system.endpoint', ''),
        (int) config('system.timeout', 10),
        (int) config('system.retries', 2)
      );
    });

    $this->app->singleton(SystemManager::class, function ($app) {
      return new SystemManager($app, $app->make(ScheduleEvaluator::class));
    });

    $this->app->singleton(PseudoCronDispatcher::class, function ($app) {
      return new PseudoCronDispatcher(
        $app->make(SystemManager::class)->buildRunUrl(),
        1
      );
    });
  }

  public function boot(): void
  {
    if ($this->app->runningInConsole()) {
      $this->publishes([
        __DIR__ . '/../config/system.php' => config_path('system.php'),
      ], 'system-config');
    }

    $this->loadRoutesFrom(__DIR__ . '/../routes/system.php');

    if ($this->app->runningInConsole()) {
      $this->commands([
        SendDiagnosticsCommand::class,
      ]);
    }

    $this->registerSchedule();
    $this->registerPseudoCronMiddleware();
  }

  private function registerSchedule(): void
  {
    $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
      if (!$this->app->make(SystemManager::class)->isEnabled()) {
        return;
      }

      $day = (int) config('system.schedule.day', 1);
      $time = (string) config('system.schedule.time', '03:00');

      $event = $schedule->command('system:send-diagnostics')
        ->weeklyOn($day, $time)
        ->withoutOverlapping()
        ->onOneServer();

      if (method_exists($event, 'name')) {
        $event->name('quickweb-system-diagnostics');
      }
    });
  }

  private function registerPseudoCronMiddleware(): void
  {
    if (!config('system.pseudo_cron.enabled', true)) {
      return;
    }

    $group = (string) config('system.pseudo_cron.middleware_group', 'web');
    $router = $this->app['router'];

    if (method_exists($router, 'pushMiddlewareToGroup')) {
      $router->pushMiddlewareToGroup($group, TriggerDiagnosticsMiddleware::class);

      return;
    }

    $this->app->booted(function () use ($group) {
      if (!$this->app->bound(Kernel::class)) {
        return;
      }

      $kernel = $this->app->make(Kernel::class);

      if (method_exists($kernel, 'appendMiddlewareToGroup')) {
        $kernel->appendMiddlewareToGroup($group, TriggerDiagnosticsMiddleware::class);
      }
    });
  }
}
