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
    const FIELD_LIST = ['name', 'path', 'split_repository'];

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
        $packageListInfos = [];
        foreach ($this->packageHelper->getPackages() as $package) {
            $packageListInfos[$package->name] = array_reduce(
                self::FIELD_LIST,
                function (array $carry, string $field) use ($package): array {
                    $carry[$field] = match ($field) {
                        'name' => $package->name,
                        'path' => $package->path,
                        'split_repository' => [
                            'organization' => $package->splitRepository->organization,
                            'name' => $package->splitRepository->name,
                        ],
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
            $headers = ['Name', 'Path', 'Split repository'];
            $rows = [];
            foreach ($packageListInfos as $item) {
                $rows[] = [
                    $item['name'],
                    $item['path'],
                    sprintf(
                        '%s/%s',
                        $item['split_repository']['organization'],
                        $item['split_repository']['name']
                    )
                ];
            }

            $this->symfonyStyle->table($headers, $rows);
        }

        return Command::SUCCESS;
    }
}
