<?php
declare (strict_types=1);

namespace PhpJsonRpc\Builder\Command;

use Exception;
use MonorepoBuilderPrefix202304\Symfony\Component\Console\Command\Command;
use MonorepoBuilderPrefix202304\Symfony\Component\Console\Input\InputInterface;
use MonorepoBuilderPrefix202304\Symfony\Component\Console\Input\InputOption;
use MonorepoBuilderPrefix202304\Symfony\Component\Console\Output\OutputInterface;
use MonorepoBuilderPrefix202304\Symplify\PackageBuilder\Console\Command\AbstractSymplifyCommand;
use PhpJsonRpc\Builder\Helper\PackageHelper;
use function explode;
use function str_replace;

class ListPackagesCommand extends AbstractSymplifyCommand
{
    const FIELD_LIST = ['name', 'path', 'vendor', 'short_name'];

    public function __construct(
        private readonly PackageHelper $packageHelper,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('packages:list')
            ->setDescription('Output list of known packages')
            ->addOption('json', null, InputOption::VALUE_NONE, 'Output the list as JSON object');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $displayFieldList = self::FIELD_LIST;
        $packageListInfos = [];
        foreach ($this->packageHelper->getPackages() as $package) {
            [$vendor, $shortName] = explode('/', $package->name);
            $packageListInfos[$package->name] = array_reduce(
                $displayFieldList,
                function (array $carry, string $field) use ($package, $vendor, $shortName): array {
                    $carry[$field] = match ($field) {
                        'name' => $package->name,
                        'path' => $package->path,
                        'vendor' => $vendor,
                        'short_name' => $shortName,
                        default => throw new Exception(sprintf('Unknown field "%s"', $field))
                    };

                    return $carry;
                },
                [],
            );
        }

        if($input->getOption('json')) {
            $this->symfonyStyle->writeln(
                json_encode($packageListInfos)
            );
        } else {
            $this->symfonyStyle->writeln('List of known packages:');
            $headers = array_map(static fn ($field) => ucfirst(str_replace('_', ' ', $field)), $displayFieldList);
            $this->symfonyStyle->table($headers, $packageListInfos);
        }

        return Command::SUCCESS;
    }
}
