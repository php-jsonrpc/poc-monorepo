<?php
declare(strict_types=1);

namespace PhpJsonRpc\Builder\Changelog;

use Symfony\Component\Yaml\Yaml;

class Changelog
{
    /**
     * @param array<Release> $releases
     */
    public function __construct(
        public readonly array $releases = [],
        public readonly ?string $ancestor = null
    ) {
    }

    public function findRelease($version): ?Release {
        foreach ($this->releases as $release) {
            if ($release->version === $version) {
                return $release;
            }
        }

        return null;
    }

    public static function fromArray(array $data): self {
        $releases = [];
        foreach ($data['releases'] ?? [] as $version => $releaseData) {
            $releases[] = Release::fromArray($version, $releaseData);
        }

        return new self($releases, $data['ancestor'] ?? null);
    }

    public static function load(string $content): self {
        $data = Yaml::parse($content);

        if (!is_array($data)) {
            throw new \Exception(sprintf('Expected an array but got %s', gettype($data)));
        }

        return self::fromArray($data);
    }
}
