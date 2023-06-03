<?php
declare (strict_types=1);

namespace PhpJsonRpc\Builder\Command;

use MonorepoBuilderPrefix202304\Symfony\Component\Console\Command\Command;
use MonorepoBuilderPrefix202304\Symfony\Component\Console\Input\InputInterface;
use MonorepoBuilderPrefix202304\Symfony\Component\Console\Input\InputOption;
use MonorepoBuilderPrefix202304\Symfony\Component\Console\Output\OutputInterface;
use MonorepoBuilderPrefix202304\Symfony\Component\Finder\Finder;
use MonorepoBuilderPrefix202304\Symplify\PackageBuilder\Console\Command\AbstractSymplifyCommand;
use PhpJsonRpc\Builder\Helper\BumpHelper;
use PhpJsonRpc\Builder\Helper\YamlHelper;
use PhpJsonRpc\Builder\Model\Changelog\BumpEnum;
use PhpJsonRpc\Builder\Model\Changelog\Changes;
use PhpJsonRpc\Builder\Model\Changelog\Fragment;
use Symfony\Component\Console\Input\InputArgument;
use Throwable;
use function sprintf;
use function ucfirst;
use const DIRECTORY_SEPARATOR;

class ChangelogFragmentsRequiredBumpCommand extends AbstractSymplifyCommand
{
    protected function configure()
    {
        $this->setName('changelog:fragments:required-bump')
            ->addArgument('package', InputArgument::REQUIRED, 'Path to the package to analyse')
            ->addOption('fragments-dir', 'fp', InputOption::VALUE_REQUIRED, 'Path to fragment directory (relative to package path!). Default to "changelogs/fragments"', 'changelogs/fragments')
            ->addOption('raw', null, InputOption::VALUE_NONE, 'Output required as raw string');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pkgPath = (string)$input->getArgument('package');
        $changelogFragmentsDirectoryName = (string)$input->getOption('fragments-dir');
        $isRawOutput = $input->getOption('raw');

        if (false === $this->smartFileSystem->exists($pkgPath)) {
            $this->symfonyStyle->error(sprintf('Package "%s" doesn\'t exist', $pkgPath));

            return Command::FAILURE;
        }

        $changelogFragmentsDirectoryPath = implode(DIRECTORY_SEPARATOR, [$pkgPath, $changelogFragmentsDirectoryName]);
        if (false === $this->smartFileSystem->exists($changelogFragmentsDirectoryPath)) {
            $this->symfonyStyle->error(sprintf('Changelog fragment directory "%s" doesn\'t exist', $changelogFragmentsDirectoryPath));

            return Command::FAILURE;
        }

        // Load fragments one by one and guess required bump for each of them
        $finder = new Finder();
        $finder->in($changelogFragmentsDirectoryPath)
            ->files()->ignoreVCSIgnored(true)
            ->name(['*.yaml', '*.yml'])
            ->sortByCaseInsensitiveName();

        $this->debugMessage(sprintf('Found %d fragments', $finder->count()));

        $requiredBump = BumpHelper::findRequiredIn($this->createIterator($finder));

        if (true === $isRawOutput) {
            $this->symfonyStyle->writeln($requiredBump->name);
        } else {
            $this->symfonyStyle->success(sprintf('%s bump required based on fragments', $requiredBump === BumpEnum::none ? 'No' : ucfirst($requiredBump->name)));
        }

        return Command::SUCCESS;
    }

    protected function createIterator(Finder $finder): iterable
    {
        foreach ($finder as $file) {
            $this->debugMessage(sprintf('Load %s', $file->getFilename()));
            try {
                $decoded = YamlHelper::loadAsYaml($file->getContents());
            } catch (Throwable $e) {
                $this->symfonyStyle->error(sprintf('Unable to load fragment "%s": %s', $file->getPathname(), $e->getMessage()));

                return Command::FAILURE;
            }

            $fragment = Fragment::fromArray($decoded);

            yield $this->guessBump($fragment->changes);
        }
    }

    protected function guessBump(?Changes $changes): BumpEnum
    {
        if (false === empty($changes?->breakingChanges ?? [])) {
            return BumpEnum::major;
        } else if (false === empty($changes?->majorChanges ?? [])) {
            return BumpEnum::minor;
        } else if (false === empty($changes?->minorChanges ?? [])
            || false === empty($changes?->deprecatedFeatures ?? [])
            || false === empty($changes?->removedFeatures ?? [])
            || false === empty($changes?->bugfixes ?? [])
        ) {
            return BumpEnum::patch;
        }

        return BumpEnum::none;
    }

    private function debugMessage(string $message, string $method = 'comment')
    {
        if (true === $this->symfonyStyle->isVerbose()
            || true === $this->symfonyStyle->isVeryVerbose()
            || true === $this->symfonyStyle->isDebug()
        ) {
            $this->symfonyStyle->$method($message);
        }
    }
}
