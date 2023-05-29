<?php
declare (strict_types=1);

namespace PhpJsonRpc\Builder\Model\Changelog;

use ValueError;
use function strtolower;

enum BumpEnum: int
{
    case major = 3;
    case minor = 2;
    case patch = 1;
    case none = 0;

    public static function fromString(string $value): BumpEnum {
        return match (strtolower($value)) {
            'major' => self::major,
            'minor' => self::minor,
            'patch' => self::patch,
            'none' => self::none,
            default => throw new ValueError(sprintf('Unknown value "%s"', $value))
        };
    }
}
