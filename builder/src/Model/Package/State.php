<?php
declare(strict_types=1);

namespace PhpJsonRpc\Builder\Model\Package;

enum State: string
{
     case Unknown = 'unknown';
     case None = 'none';
     case Updated = 'updated';
}
