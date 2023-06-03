<?php
declare(strict_types=1);

namespace PhpJsonRpc\Builder\Helper;

use PhpJsonRpc\Builder\Model\Changelog\BumpEnum;

class BumpHelper
{
    public static function findRequiredIn(iterable $list): BumpEnum
    {
        $required = BumpEnum::none;
        foreach ($list as $bump) {
            if ($bump->value > $required->value) {
                $required = $bump;
                if ($required === BumpEnum::major) {
                    break; // No need to go further, already at top value
                }
            }
        }

        return $required;
    }
}
