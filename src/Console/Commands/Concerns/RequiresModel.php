<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Console\Commands\Concerns;

use Dwarf\MeiliTools\Helpers;

trait RequiresModel
{
    /**
     * Get model class.
     *
     * @return string
     */
    protected function getModel(): string
    {
        $model = $this->argument('model') ?? $this->ask('What is the model class?');

        return Helpers::guessModelNamespace($model);
    }
}
