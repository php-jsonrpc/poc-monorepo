<?php
declare(strict_types=1);

namespace PhpJsonRpc\Builder\ReleaseWorker;

use PharIo\Version\Version;
use Symplify\MonorepoBuilder\FileSystem\ComposerJsonProvider;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\StageAwareInterface;
use Symplify\MonorepoBuilder\Release\ValueObject\Stage;

class Test4Worker implements ReleaseWorkerInterface,StageAwareInterface
{
    public function __construct(
        private readonly ComposerJsonProvider $composerJsonProvider
    ) {
    }

    public function getDescription(Version $version) : string
    {
        return 'My description4';
    }

    public function work(Version $version): void
    {
        var_dump('Yeah4');
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return "release";
    }
}
