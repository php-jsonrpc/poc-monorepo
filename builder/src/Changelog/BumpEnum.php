<?php
declare (strict_types=1);

namespace PhpJsonRpc\Builder\Changelog;

enum BumpEnum: string
{
    case major = 'major';
    case minor = 'minor';
    case patch = 'patch';
    case none = 'none';
}
