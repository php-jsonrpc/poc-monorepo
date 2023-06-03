<?php
declare(strict_types=1);

namespace PhpJsonRpc\Builder\Helper;

use PhpJsonRpc\Builder\Model\Package;
use Symplify\MonorepoBuilder\ComposerJsonManipulator\FileSystem\JsonFileManager;
use Symplify\MonorepoBuilder\FileSystem\ComposerJsonProvider;

class PackageHelper
{
    private ?array $cache = null;
    public function __construct(
        private readonly ComposerJsonProvider $composerJsonProvider,
        private readonly JsonFileManager $jsonFileManager,
    ) {
    }

    /**
     * @return iterable<string, Package> package composer file as SmartFileInfo instance indexed by package name
     */
    public function getPackages(): iterable
    {
        if (null === $this->cache) {
            $this->cache = [];
            $composerFileList = $this->composerJsonProvider->getPackagesComposerFileInfos();
            foreach ($composerFileList as $composerFile) {
                $composerInfos = $this->jsonFileManager->loadFromFileInfo($composerFile);

                $pkgName = $composerInfos['name'] ?? null;
                if (null !== $pkgName) {
                    $package = new Package($pkgName, $composerFile);
                    $this->cache[$pkgName] = $package;
                    yield $pkgName => $package;
                }
            }
        } else {
            return new \ArrayIterator($this->cache);
        }
    }
}
