<?php
declare(strict_types=1);

namespace PhpJsonRpc\Builder\ReleaseWorker;

use PharIo\Version\Version;
use Symfony\Component\Process\Process;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\StageAwareInterface;

abstract class AbstractEnsureChangelogFragmentsIntegrityWorker implements ReleaseWorkerInterface,StageAwareInterface
{
    const CHANGELOG_DIRECTORY_PATH = 'changelogs';

    public function __construct(
    ) {

    }
    public function getDescription(Version $version) : string
    {
        return 'Ensure changelog fragments integrity';
    }

    public function work(Version $version): void
    {
        $process = new Process(['make', 'lint'], self::CHANGELOG_DIRECTORY_PATH);
        $exitCode = $process->run();
        if ($exitCode !== 0) {
            throw new \Exception(
                sprintf(
                    'Error when linting changelog fragments. code: %d, err: %s',
                    $exitCode,
                    $process->getErrorOutput()
                )
            );
        }
    }
}
