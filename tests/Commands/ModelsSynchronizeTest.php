<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Commands;

use Dwarf\MeiliTools\Contracts\Actions\DetailsModel;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Models\BrokenMovie;
use Dwarf\MeiliTools\Tests\Models\MeiliMovie;
use Dwarf\MeiliTools\Tests\TestCase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;

/**
 * @internal
 */
class ModelsSynchronizeTest extends TestCase
{
    /**
     * Test `meili:models:synchronize` command.
     *
     * @return void
     */
    public function testWithAdvancedSettings(): void
    {
        try {
            $defaults = Helpers::defaultSettings(Helpers::engineVersion());
            $settings = app(MeiliMovie::class)->meiliSettings();
            $changes = collect($settings)
                ->mapWithKeys(function ($value, $key) use ($defaults) {
                    $old = $defaults[$key];
                    $new = $value;

                    return [$key => $old === $new ? false : compact('old', 'new')];
                })
                ->filter()
                ->all()
            ;
            $values = Helpers::convertIndexChangesToTable($changes);

            $details = $this->app->make(DetailsModel::class)(MeiliMovie::class);
            $this->assertSame($defaults, $details);

            $message = version_compare(app()->version(), '9.0.0', '<')
                ? 'The given data was invalid.'
                : 'The distinct attribute must be a string.';

            $this->artisan('meili:models:synchronize')
                ->expectsOutput('Processed ' . BrokenMovie::class)
                ->expectsOutput(sprintf("Exception '%s' with message '%s'", ValidationException::class, $message))
                ->expectsOutput('Processed ' . MeiliMovie::class)
                ->expectsTable(['Setting', 'Old', 'New'], $values)
                ->assertSuccessful()
            ;

            $details = $this->app->make(DetailsModel::class)(MeiliMovie::class);
            $this->assertSame($settings, Arr::except($details, ['faceting', 'pagination', 'typoTolerance']));
        } finally {
            $this->deleteIndex(app(BrokenMovie::class)->searchableAs());
            $this->deleteIndex(app(MeiliMovie::class)->searchableAs());
        }
    }

    /**
     * Test `meili:models:synchronize` command with pretend option.
     *
     * @return void
     */
    public function testWithPretend(): void
    {
        try {
            $defaults = Helpers::defaultSettings(Helpers::engineVersion());
            $settings = app(MeiliMovie::class)->meiliSettings();
            $changes = collect($settings)
                ->mapWithKeys(function ($value, $key) use ($defaults) {
                    $old = $defaults[$key];
                    $new = $value;

                    return [$key => $old === $new ? false : compact('old', 'new')];
                })
                ->filter()
                ->all()
            ;
            $values = Helpers::convertIndexChangesToTable($changes);

            $details = $this->app->make(DetailsModel::class)(MeiliMovie::class);
            $this->assertSame($defaults, $details);

            $path = __DIR__ . '/../Models';
            $namespace = 'Dwarf\\MeiliTools\\Tests\\Models';
            config(['meilitools.paths' => [$path => $namespace]]);

            $message = version_compare(app()->version(), '9.0.0', '<')
                ? 'The given data was invalid.'
                : 'The distinct attribute must be a string.';

            $this->artisan('meili:models:synchronize', ['--pretend' => true])
                ->expectsOutput('Processed ' . BrokenMovie::class)
                ->expectsOutput(sprintf("Exception '%s' with message '%s'", ValidationException::class, $message))
                ->expectsOutput('Processed ' . MeiliMovie::class)
                ->expectsTable(['Setting', 'Old', 'New'], $values)
                ->assertSuccessful()
            ;

            $details = $this->app->make(DetailsModel::class)(MeiliMovie::class);
            $this->assertSame($defaults, $details);
        } finally {
            $this->deleteIndex(app(BrokenMovie::class)->searchableAs());
            $this->deleteIndex(app(MeiliMovie::class)->searchableAs());
        }
    }

    /**
     * Test `meili:models:synchronize` command in production mode.
     *
     * @return void
     */
    public function testInProductionMode(): void
    {
        App::detectEnvironment(fn () => 'production');

        try {
            $this->artisan('meili:models:synchronize')
                ->expectsConfirmation('Do you really wish to run this command?', 'no')
                ->assertFailed()
            ;

            $this->artisan('meili:models:synchronize', ['--force' => true])
                ->assertSuccessful()
            ;

            $this->artisan('meili:models:synchronize')
                ->expectsConfirmation('Do you really wish to run this command?', 'yes')
                ->assertSuccessful()
            ;
        } finally {
            $this->deleteIndex(app(BrokenMovie::class)->searchableAs());
            $this->deleteIndex(app(MeiliMovie::class)->searchableAs());
        }
    }
}
