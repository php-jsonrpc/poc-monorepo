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
            ->addOption('raw', null, InputOption::VALUE_NONE, 'Output required as raw string')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pkgPath = (string)$input->getArgument('package');
        $changelogFragmentsDirectoryName = (string)$input->getOption('fragments-dir');
        $isRawOutput = $input->getOption('raw');

        if (!$this->smartFileSystem->exists($pkgPath)) {
            $this->symfonyStyle->error(sprintf('Package "%s" doesn\'t exist', $pkgPath));

            return Command::FAILURE;
        }

        $changelogFragmentsDirectoryPath = implode(DIRECTORY_SEPARATOR, [$pkgPath, $changelogFragmentsDirectoryName]);
        if (!$this->smartFileSystem->exists($changelogFragmentsDirectoryPath)) {
            $this->symfonyStyle->error(sprintf('Changelog fragment directory "%s" doesn\'t exist', $changelogFragmentsDirectoryPath));

            return Command::FAILURE;
        }

        // Load fragments one by one and guess required bump for each of them
        $finder = new Finder();
        $finder->in($changelogFragmentsDirectoryPath)
            ->files()->ignoreVCSIgnored(true)
            ->name(['*.yaml', '*.yml'])
            ->sortByCaseInsensitiveName()
        ;
        $this->debugMessage(sprintf('Found %d fragments', $finder->count()));

        $requiredBump = BumpHelper::findRequiredIn($this->createIterator($finder));

        if ($isRawOutput) {
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

    protected function guessBump(?Changes $changes): BumpEnum {
        if (count($changes?->breakingChanges ?? []) > 0) {
            return BumpEnum::major;
        } else if (count($changes?->majorChanges ?? []) > 0) {
            return BumpEnum::minor;
        } else if (
            count($changes?->minorChanges ?? []) > 0
            || count($changes?->deprecatedFeatures ?? []) > 0
            || count($changes?->removedFeatures ?? []) > 0
            || count($changes?->bugfixes ?? []) > 0
        ) {
            return BumpEnum::patch;
        }

        return BumpEnum::none;
    }

    private function debugMessage(string $message, string $method = 'comment')
    {
        if (!$this->symfonyStyle->isVerbose() && !$this->symfonyStyle->isVeryVerbose() && !$this->symfonyStyle->isDebug()) {
            return;
        }

        $this->symfonyStyle->$method($message);
    }
}
