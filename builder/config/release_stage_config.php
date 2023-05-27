<?php
declare(strict_types=1);

use Symplify\MonorepoBuilder\Config\MBConfig;
use PhpJsonRpc\Builder\ReleaseWorker as Worker;

return static function (MBConfig $config): void {
    // Release workers in order to execute
    $config->workers([
        Worker\Test4Worker::class,
        Worker\Test2Worker::class,
    ]);
};
