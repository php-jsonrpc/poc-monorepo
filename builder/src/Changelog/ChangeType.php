<?php
declare(strict_types=1);

namespace PhpJsonRpc\Builder\Changelog;

enum ChangeType: string
{
    case breakingChanges = 'breaking_changes';
    case securityFixes = 'security_fixes';
    case majorChanges = 'major_changes';
    case minorChanges = 'minor_changes';
    case deprecatedFeatures = 'deprecated_features';
    case removedFeatures = 'removed_features';
    case bugfixes = 'bugfixes';
}
