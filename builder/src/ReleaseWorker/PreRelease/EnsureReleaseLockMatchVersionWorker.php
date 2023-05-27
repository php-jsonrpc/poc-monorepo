<?php
declare(strict_types=1);

namespace PhpJsonRpc\Builder\ReleaseWorker\PreRelease;

use MonorepoBuilderPrefix202304\Symplify\SmartFileSystem\SmartFileSystem;
use PharIo\Version\Version;
use PhpJsonRpc\Builder\VersionLock\ShKeys;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\ReleaseWorkerInterface;
use Symplify\MonorepoBuilder\Release\Contract\ReleaseWorker\StageAwareInterface;

class EnsureReleaseLockMatchVersionWorker implements ReleaseWorkerInterface,StageAwareInterface
{
    const LOCK_FILE_PATH = 'config/version.lock.sh';

    public function __construct(
        private readonly SmartFileSystem $fs,
    ) {

    }
    public function getDescription(Version $version) : string
    {
        return sprintf('Ensure %s is the current dev version', $version->getVersionString());
    }

    public function work(Version $version): void
    {
        if (!$this->fs->exists(self::LOCK_FILE_PATH)) {
            throw new \Exception(sprintf('Lock file %s doesn\'t exist', self::LOCK_FILE_PATH));
        }
        $content = $this->fs->readFile(self::LOCK_FILE_PATH);
        $pattern = sprintf('/^%s=(.+)$/m', ShKeys::CURRENT->value);
        $count = preg_match($pattern, $content, $matches);
        if ($count === false) {
            throw new \Exception(sprintf('Error during locked version retrieval: %s', preg_last_error_msg()));
        } else if ($count === 0) {
            throw new \Exception('Unable to retrieve locked version');
        }
        $lockedVersion = new Version($matches[1]);
        // Remove dev suffix from locked version
        $stableLockedVersion = new Version(
            sprintf(
                '%d.%d.%d',
                $lockedVersion->getMajor()->getValue(),
                $lockedVersion->getMinor()->getValue(),
                $lockedVersion->getPatch()->getValue()
            )
        );
        if (!$version->equals($stableLockedVersion)) {
            throw new \Exception(
                sprintf(
                    'Expected version %s (locked at %s), got %s',
                    $stableLockedVersion->getVersionString(),
                    $lockedVersion->getVersionString(),
                    $version->getVersionString()
                )
            );
        }
        if (!$lockedVersion->hasPreReleaseSuffix() || $lockedVersion->getPreReleaseSuffix()->getValue() !== 'dev') {
            throw new \Exception(sprintf('Current version (%s) is not a dev version', $lockedVersion->getVersionString()));
        }
    }

    public function getStage(): string
    {
        return "pre-release";
    }
}
