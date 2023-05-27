<?php
declare(strict_types=1);

namespace PhpJsonRpc\Builder\ReleaseWorker;

use PharIo\Version\Version;
use Symfony\Component\Process\Process;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\StageAwareInterface;

abstract class AbstractEnsureBumpValidityWorker implements ReleaseWorkerInterface,StageAwareInterface
{
    const CONFIG_DIRECTORY_PATH = 'changelogs';

    public function __construct(
    ) {

    }
    public function getDescription(Version $version) : string
    {
        return 'Check if version bump is valid according to changelog fragments';
    }

    public function work(Version $version): void
    {
        $process = new Process(['make', '_ensure-bump-validity', sprintf('bump=%s', $bump)], self::CONFIG_DIRECTORY_PATH);
        $exitCode = $process->run();
        if ($exitCode !== 0) {
            throw new \Exception(
                sprintf(
                    'Error when re-generating lock file. code: %d, err: %s',
                    $exitCode,
                    $process->getErrorOutput()
                )
            );
        }
    }
}
