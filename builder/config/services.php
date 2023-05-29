<?php

use PhpJsonRpc\Builder\Command as Commands;
use PhpJsonRpc\Builder\Helper as Helpers;
use Symfony\Component\Filesystem\Filesystem;
use Symplify\MonorepoBuilder\Config\MBConfig;
use Symplify\MonorepoBuilder\ValueObject\Option;

return static function (MBConfig $config): void {

    $config->import(__DIR__ . '/../../vendor/autoload.php');
    $parameters = $config->parameters();

    $parameters->set(Option::IS_STAGE_REQUIRED, true);
    //$parameters->set(Option::STAGES_TO_ALLOW_EXISTING_TAG, ['pre-release', 'release', 'post-release']);
    $parameters->set('monorepo_package_name', 'php-jsonrpc/poc-monorepo');

    $config->import(__DIR__ . '/pre-release_stage_config.php');
    $config->import(__DIR__ . '/release_stage_config.php');
    $config->import(__DIR__ . '/post-release_stage_config.php');

    $services = $config->services();
    $services->defaults()->autoconfigure(true)->autowire(true);
    $services->set(Filesystem::class);
    $services->load('PhpJsonRpc\\Builder\\', __DIR__ . '/../src');
    //$services->set(Commands\ListPackagesCommand::class);
    //$services->set(Helpers\PackageHelper::class);
};
