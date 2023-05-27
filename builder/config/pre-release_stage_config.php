<?php
declare(strict_types=1);

use Symplify\MonorepoBuilder\Config\MBConfig;
use PhpJsonRpc\Builder\ReleaseWorker\PreRelease as Worker;

return static function (MBConfig $config): void {
    // Pre-release workers in order to execute
    $config->workers([
        Worker\ReGenerateVersionLockFileWorker::class,
        Worker\EnsureReleaseLockMatchVersionWorker::class,
        Worker\EnsureChangelogFragmentsIntegrity::class,
        Worker\EnsureBumpValidityWorker::class,
    ]);
};
