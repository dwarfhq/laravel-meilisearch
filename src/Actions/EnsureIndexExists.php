<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Contracts\Actions\EnsuresIndexExists;
use Dwarf\MeiliTools\Helpers;
use Laravel\Scout\EngineManager;
use MeiliSearch\Exceptions\ApiException;

/**
 * Ensure index exists.
 */
class EnsureIndexExists implements EnsuresIndexExists
{
    /**
     * Scout engine manager.
     *
     * @var \Laravel\Scout\EngineManager
     */
    protected EngineManager $manager;

    /**
     * Constructor.
     *
     * @param \Laravel\Scout\EngineManager $manager Scout engine manager.
     */
    public function __construct(EngineManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Dwarf\MeiliTools\Exceptions\MeiliToolsException When not using the MeiliSearch Scout driver.
     * @throws \MeiliSearch\Exceptions\CommunicationException   When connection to MeiliSearch fails.
     */
    public function __invoke(string $index, array $options = []): void
    {
        Helpers::throwUnlessMeiliSearch();

        $engine = $this->manager->engine();

        try {
            $info = $engine->index($index)->fetchRawInfo();
        } catch (ApiException $e) {
            $task = $engine->createIndex($index, $options);
            $engine->waitForTask($task['taskUid']);
        }
    }
}
