<?php
declare(strict_types=1);

use Symplify\MonorepoBuilder\Config\MBConfig;

return static function (MBConfig $config): void {
    // Release workers in order to execute
    $config->workers([
    ]);
};
