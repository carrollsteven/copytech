<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class NioAddonsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        try {
            if (!empty($addons = available_modules('addon'))) {
                foreach ($addons as $addon) {
                    if (file_exists($provider = base_path(implode(DIRECTORY_SEPARATOR, ['nioaddons',$addon, 'Provider', 'RouteServiceProvider.php'])))) {
                        $this->app->register("\NioAddons\\$addon\Provider\RouteServiceProvider");
                    }

                    if (file_exists($views = base_path(implode(DIRECTORY_SEPARATOR, ['nioaddons', $addon, 'Views'])))) {
                        $this->loadViewsFrom($views, $addon);
                    }

                    if (file_exists($config = base_path(implode(DIRECTORY_SEPARATOR, ['nioaddons', $addon, 'Config', 'addon.php'])))) {
                        $this->mergeConfigFrom($config, 'modules');
                    }

                    if (file_exists($migrations = base_path(implode(DIRECTORY_SEPARATOR, ['nioaddons', $addon, 'Database', 'migrations'])))) {
                        $this->loadMigrationsFrom($migrations);
                    }

                    if (class_exists($addonLoader = "\\NioAddons\\{$addon}\\{$addon}")) {
                        $this->app->bind(strtolower($addon), function () use ($addonLoader) {
                            return new $addonLoader();
                        });
                    }

                    if (file_exists($eventProvider = base_path(implode(DIRECTORY_SEPARATOR, ['nioaddons',$addon, 'Provider', 'EventServiceProvider.php'])))) {
                        $this->app->register("\NioAddons\\$addon\Provider\EventServiceProvider");
                    }
                }
            }
        } catch (\Exception $e) {
            if (env('APP_DEBUG', false)) {
                save_error_log($e, 'addon-service');
            }
        }
    }
}
