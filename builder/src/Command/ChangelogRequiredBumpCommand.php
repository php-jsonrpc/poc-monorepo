<?php
declare (strict_types=1);

namespace PhpJsonRpc\Builder\Command;

use PhpJsonRpc\Builder\Changelog\BumpEnum;
use PhpJsonRpc\Builder\Changelog\Changelog;
use PhpJsonRpc\Builder\Changelog\Changes;
use PhpJsonRpc\Builder\Changelog\Release;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

// the name of the command is what users type after "php bin/console"
#[AsCommand(name: 'changelog:required-bump')]
class ChangelogRequiredBumpCommand extends Command
{
    const FILE_PATH = '../changelogs/changelog.yaml';

    protected function configure()
    {
        $this->addArgument('version', InputArgument::REQUIRED, 'Version to analyse');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $version = (string)$input->getArgument('version');

        $symfonyStyle = new SymfonyStyle($input, $output);
        $fs = new Filesystem();

        // Check if file exists
        if (!$fs->exists(self::FILE_PATH)) {
            $symfonyStyle->error(sprintf('Changelog %s doesn\'t exist', self::FILE_PATH));

            return Command::FAILURE;
        }

        // Load changelogs.yaml
        $content = file_get_contents(self::FILE_PATH);
        if (false === $content) {
            $symfonyStyle->error('Unable to load changelog');

            return Command::FAILURE;
        }
        $changelog = Changelog::load($content);

        $stableVersion = $changelog->findRelease($version);
        $devVersion = $changelog->findRelease($version);
        if (null === $stableVersion && null === $stableVersion) {
            $symfonyStyle->error('Unable to find version');

            return Command::FAILURE;
        }

        $requiredBump = $this->guessBump($stableVersion?->changes, $devVersion?->changes);

        $symfonyStyle->writeln($requiredBump->name);

        return Command::SUCCESS;
    }

    protected function guessBump(?Changes $stableVersionChanges, ?Changes $devVersionChanges): BumpEnum {
        if (
            count($stableVersionChanges?->breakingChanges ?? []) > 0
            || count($devVersionChanges?->breakingChanges ?? []) > 0
        ) {
            return BumpEnum::major;
        } else if (
            count($stableVersionChanges?->majorChanges ?? []) > 0
            || count($devVersionChanges?->majorChanges ?? []) > 0
        ) {
            return BumpEnum::minor;
        } else if (
            count($stableVersionChanges?->minorChanges ?? []) > 0
            || count($devVersionChanges?->minorChanges ?? []) > 0
            || count($stableVersionChanges?->deprecatedFeatures ?? []) > 0
            || count($devVersionChanges?->deprecatedFeatures ?? []) > 0
            || count($stableVersionChanges?->removedFeatures ?? []) > 0
            || count($devVersionChanges?->removedFeatures ?? []) > 0
            || count($stableVersionChanges?->bugfixes ?? []) > 0
            || count($devVersionChanges?->bugfixes ?? []) > 0
        ) {
            return BumpEnum::patch;
        }

        return BumpEnum::none;
    }
}
