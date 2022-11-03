<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Commands;

use Dwarf\MeiliTools\Tests\TestCase;

/**
 * @internal
 */
class IndexesListTest extends TestCase
{
    /**
     * Test index.
     *
     * @var string
     */
    private const INDEX = 'testing-indexes-list';

    /**
     * Test `meili:indexes:list` command with default settings.
     *
     * @return void
     */
    public function testWithDefaultSettings(): void
    {
        $this->withIndex(self::INDEX, function () {
            // Since data returned from MeiliSearch includes microsecond precision timestamps,
            // it's impossible to validate the exact console output.
            $this->artisan('meili:indexes:list')
                // ->expectsOutputToContain(self::INDEX) - Laravel 9 only.
                ->assertSuccessful()
            ;
        });
    }

    /**
     * Test `meili:indexes:list` command with stats option.
     *
     * @return void
     */
    public function testWithStats(): void
    {
        $this->withIndex(self::INDEX, function () {
            // Since data returned from MeiliSearch includes microsecond precision timestamps,
            // it's impossible to validate the exact console output.
            $this->artisan('meili:indexes:list', ['--stats' => true])
                // ->expectsOutputToContain(self::INDEX) - Laravel 9 only.
                ->assertSuccessful()
            ;
        });
    }
}
