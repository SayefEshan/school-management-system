<?php

namespace Modules\Settings\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Modules\Settings\Models\Setting;
use Nwidart\Modules\Traits\PathNamespace;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class SettingsServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'Settings';

    protected string $nameLower = 'settings';

    protected array $configMap = [
        'google_client_id' => 'services.google.client_id',
        'google_client_secret' => 'services.google.client_secret',
        'google_redirect_uri' => 'services.google.redirect',
        'github_client_id' => 'services.github.client_id',
        'github_client_secret' => 'services.github.client_secret',
        'github_redirect_uri' => 'services.github.redirect',
        'apple_client_id' => 'services.apple.client_id',
        'apple_client_secret' => 'services.apple.client_secret',
        'apple_redirect_uri' => 'services.apple.redirect',
        'apple_team_id' => 'services.apple.team_id',
        'apple_key_id' => 'services.apple.key_id',
        'apple_key_file' => 'services.apple.key_file',
    ];

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));

        $this->updateConfigsFromSettings();
    }


    private function updateConfigsFromSettings(): void
    {
        try {
            if (Schema::hasTable('settings')) {
                $settings = \Illuminate\Support\Facades\Cache::rememberForever('app_settings', function () {
                    return Setting::all();
                });

                // Get active mailer from the loaded collection
                $activeMailerSetting = $settings->firstWhere('key', 'email_mailer');
                $activeMailer = $activeMailerSetting ? $activeMailerSetting->value : null;

                foreach ($settings as $setting) {
                    if ($setting->key === 'email_mailers') {
                        $this->updateMailers($setting, $activeMailer);
                        continue;
                    }
                    
                    if (isset($this->configMap[$setting->key])) {
                        config([$this->configMap[$setting->key] => $setting->value]);
                    }

                    config([
                        'settings.' . $setting->key => [
                            'group' => $setting->group,
                            'type' => $setting->type,
                            'value' => $setting->value,
                            'description' => $setting->description
                        ]
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error("Error updating configs from settings: " . $e->getMessage());
        }
    }

    private function updateMailers($setting, $activeMailer): void
    {
        try {
            if (!$activeMailer) {
                return;
            }

            foreach ($setting->value as $mailer) {
                if ($mailer['TYPE'] === $activeMailer) {
                    $transport = $mailer['VALUE']['transport'];
                    config(['mail.default' => $transport]);
                    foreach ($mailer['VALUE'] as $key => $value) {
                        if (empty($transport)) {
                            continue;
                        }
                        if ($key === 'from') {
                            config(['mail.from' => $value]);
                            continue;
                        }
                        config(['mail.mailers.' . $transport . '.' . $key => $value]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Error updating configs from settings: " . $e->getMessage());
        }
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        // $this->commands([]);
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        // $this->app->booted(function () {
        //     $schedule = $this->app->make(Schedule::class);
        //     $schedule->command('inspire')->hourly();
        // });
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->nameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->nameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);
            $this->loadJsonTranslationsFrom(module_path($this->name, 'lang'));
        }
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $relativeConfigPath = config('modules.paths.generator.config.path');
        $configPath = module_path($this->name, $relativeConfigPath);

        if (is_dir($configPath)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($configPath));

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $relativePath = str_replace($configPath . DIRECTORY_SEPARATOR, '', $file->getPathname());
                    $configKey = $this->nameLower . '.' . str_replace([DIRECTORY_SEPARATOR, '.php'], ['.', ''], $relativePath);
                    $key = ($relativePath === 'config.php') ? $this->nameLower : $configKey;

                    $this->publishes([$file->getPathname() => config_path($relativePath)], $configPath);
                    $this->mergeConfigFrom($file->getPathname(), $key);
                }
            }
        }
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->nameLower);
        $sourcePath = module_path($this->name, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->nameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->nameLower);

        $componentNamespace = $this->module_namespace($this->name, $this->app_path(config('modules.paths.generator.component-class.path')));
        Blade::componentNamespace($componentNamespace, $this->nameLower);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$this->nameLower)) {
                $paths[] = $path.'/modules/'.$this->nameLower;
            }
        }

        return $paths;
    }
}
