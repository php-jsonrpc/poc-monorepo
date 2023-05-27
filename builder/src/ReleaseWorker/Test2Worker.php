<?php
declare(strict_types=1);

namespace PhpJsonRpc\Builder\ReleaseWorker;

use PharIo\Version\Version;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\StageAwareInterface;
use Symplify\MonorepoBuilder\Release\ValueObject\Stage;

final class Test2Worker implements ReleaseWorkerInterface,StageAwareInterface
{
    public function getDescription(Version $version) : string
    {
        return 'My description2';
    }

    public function work(Version $version): void
    {
        var_dump('Yeah2');
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return "another-stage";
    }
}
