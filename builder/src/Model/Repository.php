<?php
declare(strict_types=1);

namespace PhpJsonRpc\Builder\Model;

class Repository
{
    public function __construct(
        public readonly string $organization,
        public readonly string $name,
    ) {
    }
}
