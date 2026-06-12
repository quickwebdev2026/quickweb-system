<?php

namespace Quickweb\System\Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Quickweb\System\Jobs\SendDiagnosticsJob;
use Quickweb\System\Services\SystemManager;
use Quickweb\System\Tests\TestCase;

class PseudoCronTest extends TestCase
{
  public function test_run_route_triggers_job_when_due(): void
  {
    Queue::fake();

    $manager = $this->app->make(SystemManager::class);
    $token = $manager->resolveToken();

    $response = $this->post("/_system/run/{$token}");

    $response->assertStatus(204);
    Queue::assertPushed(SendDiagnosticsJob::class);
  }

  public function test_run_route_rejects_invalid_token(): void
  {
    Queue::fake();

    $response = $this->post('/_system/run/invalid-token');

    $response->assertStatus(403);
    Queue::assertNothingPushed();
  }

  public function test_web_middleware_group_includes_trigger_middleware(): void
  {
    $webMiddleware = app('router')->getMiddlewareGroups()['web'] ?? [];

    $this->assertContains(
      \Quickweb\System\Http\Middleware\TriggerDiagnosticsMiddleware::class,
      $webMiddleware
    );
  }

  public function test_middleware_skips_pseudo_cron_route_itself(): void
  {
    Http::fake();
    Queue::fake();

    $token = $this->app->make(SystemManager::class)->resolveToken();

    $this->post("/_system/run/{$token}")->assertStatus(204);

    Http::assertNothingSent();
  }
}
