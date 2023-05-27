<?php

use Symplify\MonorepoBuilder\ComposerJsonManipulator\ValueObject\ComposerJsonSection;
use Symplify\MonorepoBuilder\Config\MBConfig;
use Symplify\MonorepoBuilder\ValueObject\Option;

return static function (MBConfig $config): void {
    // where are the packages located?
    $config->packageDirectories([
        __DIR__ . '/../../packages',
    ]);
    // how to skip packages in loaded directories?
    //$config->packageDirectoriesExcludes([__DIR__ . '/packages/secret-package']);

    // ## "merge" command related
    // what extra parts to add after merge?
    $config->dataToAppend([
        ComposerJsonSection::AUTOLOAD_DEV => [
            'psr-4' => [
                'Symplify\Tests\\' => 'tests',
                'PhpJsonRpc\\Builder\\' => 'builder/src',
            ],
        ],
        ComposerJsonSection::REQUIRE_DEV => [
            "symplify/monorepo-builder" => "^11.2",
        ],
    ]);
    // what extra parts to remove after merge?
    $config->dataToRemove([
        ComposerJsonSection::REPOSITORIES => [
            Option::REMOVE_COMPLETELY, // this will remove all repositories
        ],
    ]);
};
