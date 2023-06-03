<?php
declare(strict_types=1);

namespace PhpJsonRpc\Builder\Model\Changelog;

class Changes
{
    /**
     * @param array<string> $breakingChanges
     * @param array<string> $securityFixes
     * @param array<string> $majorChanges
     * @param array<string> $minorChanges
     * @param array<string> $deprecatedFeatures
     * @param array<string> $removedFeatures
     * @param array<string> $bugfixes
     */
    public function __construct(
        public readonly array $breakingChanges,
        public readonly array $securityFixes,
        public readonly array $majorChanges,
        public readonly array $minorChanges,
        public readonly array $deprecatedFeatures,
        public readonly array $removedFeatures,
        public readonly array $bugfixes,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $breakingChanges = [];
        $securityFixes = [];
        $majorChanges = [];
        $minorChanges = [];
        $deprecatedFeatures = [];
        $removedFeatures = [];
        $bugfixes = [];
        foreach ($data as $type => $changes) {
            switch ($type) {
                case ChangeType::breakingChanges->value:
                    $breakingChanges = $changes;
                    break;
                case ChangeType::securityFixes->value:
                    $securityFixes = $changes;
                    break;
                case ChangeType::majorChanges->value:
                    $majorChanges = $changes;
                    break;
                case ChangeType::minorChanges->value:
                    $minorChanges = $changes;
                    break;
                case ChangeType::deprecatedFeatures->value:
                    $deprecatedFeatures = $changes;
                    break;
                case ChangeType::removedFeatures->value:
                    $removedFeatures = $changes;
                    break;
                case ChangeType::bugfixes->value:
                    $bugfixes = $changes;
                    break;
            }
        }

        return new self(
            $breakingChanges,
            $securityFixes,
            $majorChanges,
            $minorChanges,
            $deprecatedFeatures,
            $removedFeatures,
            $bugfixes,
        );
    }
}
