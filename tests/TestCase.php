<?php

namespace Quickweb\System\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Quickweb\System\SystemServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
  protected function getPackageProviders($app)
  {
    return [
      SystemServiceProvider::class,
    ];
  }

  protected function getEnvironmentSetUp($app)
  {
    $app['config']->set('app.key', 'base64:' . base64_encode(str_repeat('a', 32)));
    $app['config']->set('system.enabled', true);
    $app['config']->set('system.except_environments', []);
    $app['config']->set('system.endpoint', 'https://example.test/diagnostics');
    $app['config']->set('system.pseudo_cron.enabled', true);
    $app['config']->set('system.pseudo_cron.sample_rate', 1);
    $app['config']->set('system.pseudo_cron.use_background_curl', false);
    $app['config']->set('queue.default', 'sync');
    $app['config']->set('system.interval', 604800);
    $app['config']->set(
      'system.state.path',
      'system/test-' . str_replace('.', '', uniqid('', true)) . '.json'
    );
  }

  protected function defineWebRoutes($router)
  {
    $router->get('/', function () {
      return 'ok';
    });
  }
}
