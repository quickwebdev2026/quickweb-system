<?php

namespace Quickweb\System\Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Quickweb\System\Contracts\StateStoreInterface;
use Quickweb\System\Tests\TestCase;

class SendDiagnosticsTest extends TestCase
{
  public function test_it_sends_initial_diagnostics_and_persists_state(): void
  {
    Http::fake([
      'example.test/*' => Http::response(['ok' => true], 200),
    ]);

    $this->assertSame(0, Artisan::call('system:send-diagnostics'));

    Http::assertSent(function ($request) {
      $body = json_decode($request->body(), true);

      return $request->url() === 'https://example.test/diagnostics'
        && is_array($body)
        && ($body['event'] ?? null) === 'initial'
        && ($body['package']['name'] ?? null) === 'quickweb/system'
        && !empty($body['package']['version'])
        && isset($body['application']['app_name'])
        && array_key_exists('package_name', $body['application'])
        && array_key_exists('package_version', $body['application'])
        && isset($body['server']['os']);
    });

    $state = $this->app->make(StateStoreInterface::class)->get();

    $this->assertArrayHasKey('initialized_at', $state);
    $this->assertArrayHasKey('last_sent_at', $state);
    $this->assertSame('initial', $state['last_event']);
  }

  public function test_it_skips_when_not_due(): void
  {
    Http::fake();

    $store = $this->app->make(StateStoreInterface::class);
    $store->save([
      'initialized_at' => now()->toAtomString(),
      'last_sent_at' => now()->toAtomString(),
      'last_event' => 'initial',
    ]);

    $this->assertSame(0, Artisan::call('system:send-diagnostics'));

    Http::assertNothingSent();
  }

  public function test_force_option_sends_even_when_not_due(): void
  {
    Http::fake([
      'example.test/*' => Http::response(['ok' => true], 200),
    ]);

    $store = $this->app->make(StateStoreInterface::class);
    $store->save([
      'initialized_at' => now()->toAtomString(),
      'last_sent_at' => now()->toAtomString(),
      'last_event' => 'initial',
    ]);

    $this->assertSame(0, Artisan::call('system:send-diagnostics', ['--force' => true]));

    Http::assertSent(function ($request) {
      $body = json_decode($request->body(), true);

      return is_array($body) && ($body['event'] ?? null) === 'manual';
    });
  }
}
