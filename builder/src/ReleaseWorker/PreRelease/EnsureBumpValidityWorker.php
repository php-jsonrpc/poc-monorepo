<?php
declare(strict_types=1);

namespace PhpJsonRpc\Builder\ReleaseWorker\PreRelease;

use PhpJsonRpc\Builder\ReleaseWorker\AbstractEnsureBumpValidityWorker;

class EnsureBumpValidityWorker extends AbstractEnsureBumpValidityWorker
{
    public function getStage(): string
    {
        return "pre-release";
    }
}
