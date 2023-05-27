<?php
declare(strict_types=1);

namespace PhpJsonRpc\Builder\ReleaseWorker\PreRelease;

use PhpJsonRpc\Builder\ReleaseWorker\AbstractEnsureChangelogFragmentsIntegrityWorker;

class EnsureChangelogFragmentsIntegrity extends AbstractEnsureChangelogFragmentsIntegrityWorker
{
    public function getStage(): string
    {
        return "pre-release";
    }
}
