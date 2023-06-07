<?php
declare(strict_types=1);

namespace PhpJsonRpc\Builder\Model;

use MonorepoBuilderPrefix202304\Symplify\SmartFileSystem\SmartFileInfo;

class Package
{
    public readonly string $path;

    public function __construct(
        public readonly string $name,
        public readonly SmartFileInfo $composer,
        public readonly Repository $splitRepository
    ) {
        $this->path = $this->composer->getRelativeDirectoryPath();
    }
}
