<?php

namespace Mintreu\LaravelIntegration\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * Class LaravelIntegrationRegistry
 *
 * Handles retrieval of all configured providers and active provider codes.
 * Provides caching for performance and a clean API to access integration configs.
 */
class LaravelIntegrationRegistry
{
    /**
     * Create a new instance (factory method)
     */
    public static function make(): self
    {
        return new self();
    }

    /**
     * Reset the cached active providers.
     */
    public function reset(): void
    {
        Cache::forget(config('laravel-integration.cache.active_providers_key'));
        self::getActiveProviders(); // re-cache
    }

    /**
     * Get the list of active provider codes from DB/cache.
     *
     * @return string[] List of active provider codes
     */

    public static function getActiveProviders(): array
    {
        return Cache::remember(
            config('laravel-integration.cache.active_providers_key'),
            config('laravel-integration.cache.ttl_minutes'),
            function () {
                // Check if table exists
                if (!Schema::hasTable('integrations')) {
                    // fallback: return all providers from config
                    return array_keys(config('laravel-integration.providers', []));
                }

                // Check if required columns exist
                $columns = Schema::getColumnListing('integrations');
                if (!in_array('status', $columns) || !in_array('url', $columns)) {
                    // fallback: return all providers from config
                    return array_keys(config('laravel-integration.providers', []));
                }

                // Query active providers from DB
                return DB::table('integrations')
                    ->where('status', true)
                    ->pluck('url') // or use another unique identifier if needed
                    ->toArray();
            }
        );
    }




    /**
     * Get a flat registry of all providers from config.
     * Keyed by provider code with full config including keys, secrets, and ServiceProvider class.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getAllProviders(): array
    {
        return collect(config('laravel-integration.providers', []))
            ->flatMap(function ($providers, $type) {
                return collect($providers)->mapWithKeys(function ($config, $provider) use ($type) {
                    // Prefix type to avoid collisions
                    return ["{$provider}-{$type}" => $config];
                });
            })
            ->toArray();
    }


    /**
     * Get providers grouped by type.
     * Useful if you need to display or filter providers by integration type.
     *
     * @return array<string, array<string, array<string, mixed>>>
     */
    public function getProvidersByType(): array
    {
        return config('laravel-integration.providers', []);
    }
}
