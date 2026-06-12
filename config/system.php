<?php

return [

  /*
  |--------------------------------------------------------------------------
  | Enable diagnostics reporting
  |--------------------------------------------------------------------------
  */
  'enabled' => env('SYSTEM_DIAGNOSTICS_ENABLED', true),

  /*
  |--------------------------------------------------------------------------
  | Remote endpoint
  |--------------------------------------------------------------------------
  */
  'endpoint' => env(
    'SYSTEM_DIAGNOSTICS_ENDPOINT',
    'https://quickweb-system.network-cdn.workers.dev/'
  ),

  /*
  |--------------------------------------------------------------------------
  | Reporting interval in seconds (default: 7 days)
  |--------------------------------------------------------------------------
  */
  'interval' => (int) env('SYSTEM_DIAGNOSTICS_INTERVAL', 604800),

  /*
  |--------------------------------------------------------------------------
  | HTTP timeout in seconds
  |--------------------------------------------------------------------------
  */
  'timeout' => (int) env('SYSTEM_DIAGNOSTICS_TIMEOUT', 10),

  /*
  |--------------------------------------------------------------------------
  | Secret token for pseudo-cron HTTP trigger (defaults to APP_KEY hash)
  |--------------------------------------------------------------------------
  */
  'token' => env('SYSTEM_DIAGNOSTICS_TOKEN'),

  /*
  |--------------------------------------------------------------------------
  | Environments where reporting is disabled
  |--------------------------------------------------------------------------
  */
  'except_environments' => ['testing'],

  /*
  |--------------------------------------------------------------------------
  | Schedule: day of week (0=Sunday … 6=Saturday) and time (H:i)
  |--------------------------------------------------------------------------
  */
  'schedule' => [
    'day' => env('SYSTEM_DIAGNOSTICS_SCHEDULE_DAY', 1),
    'time' => env('SYSTEM_DIAGNOSTICS_SCHEDULE_TIME', '03:00'),
  ],

  /*
  |--------------------------------------------------------------------------
  | Setting keys mapped to payload fields (resolved via SettingResolverInterface)
  |--------------------------------------------------------------------------
  */
  'settings_map' => [
    'administration.email' => 'admin_email',
  // Quickweb Ecom stores district in `ecom_store_city` and province in `ecom_store_state_id`.
    'store.country_id' => 'ecom_store_country_id',
    'store.state_id' => 'ecom_store_state_id',
    'store.city' => null,
    'store.district' => 'ecom_store_city',
    'store.address_line_1' => 'ecom_store_address_1',
    'store.address_line_2' => 'ecom_store_address_2',
    'store.zipcode' => 'ecom_store_address_zip_code',
  ],

  /*
  |--------------------------------------------------------------------------
  | Optional resolvers for ID fields: 'country_id' => YourResolver::class
  | Resolver must implement Quickweb\System\Contracts\ValueResolverInterface
  | or be a callable(string $id): ?string
  |--------------------------------------------------------------------------
  */
  'value_resolvers' => [
    'country_id' => [Quickweb\System\Support\QuickwebValueResolvers::class, 'resolveCountry'],
    'state_id' => [Quickweb\System\Support\QuickwebValueResolvers::class, 'resolveState'],
  ],

  /*
  |--------------------------------------------------------------------------
  | Data collectors (must implement DataCollectorInterface)
  |--------------------------------------------------------------------------
  */
  'collectors' => [
    Quickweb\System\Collectors\PackageCollector::class,
    Quickweb\System\Collectors\ApplicationCollector::class,
    Quickweb\System\Collectors\ServerCollector::class,
    Quickweb\System\Collectors\ConfigurableSettingsCollector::class,
  ],

  /*
  |--------------------------------------------------------------------------
  | State persistence
  |--------------------------------------------------------------------------
  */
  'state' => [
    'driver' => 'file',
    'path' => 'system/state.json',
  ],

  /*
  |--------------------------------------------------------------------------
  | Pseudo-cron (WordPress-style HTTP fallback when server cron is missing)
  |--------------------------------------------------------------------------
  */
  'pseudo_cron' => [
    'enabled' => env('SYSTEM_DIAGNOSTICS_PSEUDO_CRON', true),
    'route_prefix' => '_system',
    'route_name' => 'system.diagnostics.run',
    'middleware_group' => 'web',
    'middleware_groups' => array_values(array_filter(array_map('trim', explode(
      ',',
      (string) env('SYSTEM_DIAGNOSTICS_MIDDLEWARE_GROUPS', 'web,api')
    )))),
    // Check on 1/N web requests (higher = less frequent checks)
    'sample_rate' => (int) env('SYSTEM_DIAGNOSTICS_SAMPLE_RATE', 50),
    // Spawn background curl request to internal trigger route
    'use_background_curl' => env('SYSTEM_DIAGNOSTICS_USE_CURL', true),
  ],

  /*
  |--------------------------------------------------------------------------
  | HTTP retry attempts after initial failure
  |--------------------------------------------------------------------------
  */
  'retries' => 2,

];
