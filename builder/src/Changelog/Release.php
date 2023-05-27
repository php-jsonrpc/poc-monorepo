<?php
declare(strict_types=1);

namespace PhpJsonRpc\Builder\Changelog;

class Release
{
    /**
     * @param array<string> $fragments
     */
    public function __construct(
        public readonly string $version,
        public readonly Changes $changes,
        public readonly ?string $date = null,
        public readonly array $fragments = [],
    ) {
    }

    public static function fromArray(string $version, array $data): self {

        return new self(
            $version,
            Changes::fromArray($data['changes'] ?? []),
            $data['release_data'] ?? null,
            $data['fragments'] ?? []
        );
    }
}
