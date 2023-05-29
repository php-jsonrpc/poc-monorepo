<?php
declare(strict_types=1);

namespace PhpJsonRpc\Builder\Model\Package;

use MonorepoBuilderPrefix202304\Symplify\SmartFileSystem\SmartFileInfo;

class Package
{
    public PackageState $state = PackageState::Unknown;
    public readonly string $path;

    public function __construct(
        public readonly string $name,
        public readonly SmartFileInfo $composer,
    ) {
        $this->path = $this->composer->getRelativeDirectoryPath();
    }
}
