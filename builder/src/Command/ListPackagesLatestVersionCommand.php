<?php
declare (strict_types=1);

namespace PhpJsonRpc\Builder\Command;

use Composer\Semver\Semver;
use MonorepoBuilderPrefix202304\Symfony\Component\Console\Command\Command;
use MonorepoBuilderPrefix202304\Symfony\Component\Console\Input\InputInterface;
use MonorepoBuilderPrefix202304\Symfony\Component\Console\Input\InputOption;
use MonorepoBuilderPrefix202304\Symfony\Component\Console\Output\OutputInterface;
use MonorepoBuilderPrefix202304\Symplify\PackageBuilder\Console\Command\AbstractSymplifyCommand;
use MonorepoBuilderPrefix202304\Symplify\SmartFileSystem\SmartFileInfo;
use PhpJsonRpc\Builder\Helper\PackageHelper;
use PhpJsonRpc\Builder\Model\Changelog\Changelog;
use PhpJsonRpc\Builder\Model\Changelog\Release;
use Symfony\Component\Yaml\Yaml;
use function explode;
use function gettype;
use function is_array;
use function json_encode;
use function sprintf;

class ListPackagesLatestVersionCommand extends AbstractSymplifyCommand
{
    public function __construct(
        private readonly PackageHelper $packageHelper,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('packages:version:list-latest')
            ->setDescription('Output list of known packages with their latest version')
            ->addOption('json', null, InputOption::VALUE_NONE, 'Output the list as JSON object');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var array<string, ?string> $packageListInfos */
        $packageListInfos = [];
        foreach ($this->packageHelper->getPackages() as $package) {
            $filepath = sprintf('%s/changelogs/changelog.yaml', $package->path);
            if (false === \file_exists($filepath)) {
                $packageListInfos[$package->name] = null;
                continue;
            }

            $packageListInfos[$package->name] = $this->getLatestVersion(new SmartFileInfo($filepath));
        }

        if (true === $input->getOption('json')) {
            $this->symfonyStyle->writeln(json_encode($packageListInfos));
        } else {
            $this->symfonyStyle->writeln('List of known packages latest version:');
            $headers = ['name', 'version'];
            $rows = [];
            foreach ($packageListInfos as $key => $item) {
                $rows[] = [$key, $item ?? 'None !'];
            }

            $this->symfonyStyle->table($headers, $rows);
        }

        return Command::SUCCESS;
    }

    protected function getLatestVersion(SmartFileInfo $changelog): ?string
    {
        // Load file as Yaml
        $data = Yaml::parse($changelog->getContents());

        if (true !== is_array($data)) {
            throw new \Exception(sprintf('Expected an array but got %s', gettype($data)));
        }

        $changelog = Changelog::fromArray($data);

        $versionList = array_map(static fn (Release $release) => $release->version, $changelog->releases);

        return Semver::rsort($versionList)[0] ?? null;
    }
}
