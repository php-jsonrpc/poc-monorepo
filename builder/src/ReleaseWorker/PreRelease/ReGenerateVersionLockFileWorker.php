<?php
declare(strict_types=1);

namespace PhpJsonRpc\Builder\ReleaseWorker\PreRelease;

use PhpJsonRpc\Builder\ReleaseWorker\AbstractReGenerateVersionLockFileWorker;

class ReGenerateVersionLockFileWorker extends AbstractReGenerateVersionLockFileWorker
{
    public function getStage(): string
    {
        return "pre-release";
    }
}
