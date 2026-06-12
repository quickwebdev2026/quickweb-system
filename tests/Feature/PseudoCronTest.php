<?php

namespace Quickweb\System\Tests\Feature;

use Illuminate\Support\Facades\Http;
use Quickweb\System\Contracts\StateStoreInterface;
use Quickweb\System\Services\SystemManager;
use Quickweb\System\Tests\TestCase;

class PseudoCronTest extends TestCase
{
  public function test_run_route_sends_diagnostics_when_due(): void
  {
    Http::fake([
      'example.test/*' => Http::response(['ok' => true], 200),
    ]);

    $manager = $this->app->make(SystemManager::class);
    $token = $manager->resolveToken();

    $response = $this->post("/_system/run/{$token}");

    $response->assertStatus(204);

    Http::assertSent(function ($request) {
      return $request->url() === 'https://example.test/diagnostics';
    });

    $state = $this->app->make(StateStoreInterface::class)->get();
    $this->assertSame('initial', $state['last_event'] ?? null);
  }

  public function test_run_route_rejects_invalid_token(): void
  {
    Http::fake();

    $response = $this->post('/_system/run/invalid-token');

    $response->assertStatus(403);
    Http::assertNothingSent();
  }

  public function test_web_middleware_group_includes_trigger_middleware(): void
  {
    $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
    $webMiddleware = $kernel->getMiddlewareGroups()['web'] ?? [];

    $this->assertContains(
      \Quickweb\System\Http\Middleware\TriggerDiagnosticsMiddleware::class,
      $webMiddleware
    );
  }

  public function test_middleware_skips_pseudo_cron_route_itself(): void
  {
    Http::fake();

    $token = $this->app->make(SystemManager::class)->resolveToken();

    $this->post("/_system/run/{$token}")->assertStatus(204);

    Http::assertSentCount(1);
  }
}
