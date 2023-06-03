<?php
declare(strict_types=1);

namespace PhpJsonRpc\Builder\Model\Changelog;

class Fragment
{
    public function __construct(
        public readonly Changes $changes,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(Changes::fromArray($data ?? []));
    }
}
