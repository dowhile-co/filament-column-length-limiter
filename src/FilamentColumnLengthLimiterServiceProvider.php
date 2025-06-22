<?php

namespace DefStudio\FilamentColumnLengthLimiter;

use Closure;
use Filament\Support\Contracts\HasLabel;
use Filament\Tables\Columns\TextColumn;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentColumnLengthLimiterServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-column-length-limiter';

    public static string $viewNamespace = 'filament-column-length-limiter';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasInstallCommand(function(InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('defstudio/filament-column-length-limiter');
            });

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }
    }

    public function packageRegistered(): void
    {
    }

    public function packageBooted(): void
    {
        TextColumn::macro('limitWithTooltip', function(int|null|Closure $limit = null): TextColumn {
            if ($limit === null) {
                $this
                    ->wrap()->lineClamp(1)
                    ->extraAttributes(function($state) {
                        if ($state instanceof HasLabel) {
                            $state = $state->getLabel();
                        }

                        return [
                            'x-data' => "{}",
                            'x-tooltip' => "\$el.querySelector('.fi-ta-text-item-label').offsetHeight < \$el.querySelector('.fi-ta-text-item-label').scrollHeight ? {
                                content: `$state`,
                                theme: \$store.theme,
                             }: ''",
                        ];
                    }, true);

                return $this;
            }

            /** @var TextColumn $this */
            $this->limit(fn() => $this->evaluate($limit))
                ->tooltip(function($state) use ($limit) {
                    /** @var TextColumn $this */
                    $evaluatedLimit = $this->evaluate($limit);

                    if ($evaluatedLimit === null) {
                        return false;
                    }

                    if ($state instanceof HasLabel) {
                        $state = $state->getLabel();
                    }

                    return strlen($state) > $this->evaluate($limit) ? $state : false;
                });

            return $this;
        });
    }
}
