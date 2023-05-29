<?php
declare (strict_types=1);

namespace PhpJsonRpc\Builder\Command;

use MonorepoBuilderPrefix202304\Symfony\Component\Console\Input\InputInterface;
use MonorepoBuilderPrefix202304\Symfony\Component\Console\Output\OutputInterface;
use MonorepoBuilderPrefix202304\Symplify\PackageBuilder\Console\Command\AbstractSymplifyCommand;
use PhpJsonRpc\Builder\Helper\PackageHelper;
use PhpJsonRpc\Builder\Model\Package;
use PhpJsonRpc\Builder\Model\PackageState;
use MonorepoBuilderPrefix202304\Symfony\Component\Console\Command\Command;
use MonorepoBuilderPrefix202304\Symfony\Component\Console\Input\InputOption;

class ListPackagesCommand extends AbstractSymplifyCommand
{
    public function __construct(
        private readonly PackageHelper $packageHelper,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        try {
            $this->setName('packages:list')
                ->setDescription('Output list of known packages')
                ->addOption('state-from', null, InputOption::VALUE_REQUIRED, 'Resolve package state compared to another branch', null)
                ->addOption('updated-only', null, InputOption::VALUE_NONE, 'Keep only updated packages. Require --state-from option !')
                ->addOption('json', null, InputOption::VALUE_NONE, 'Output the list as JSON object')
                ->addOption('field', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Fields to display, vendor, name and path by default', ['name', 'path'])
                ->addOption('with-field', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'List of fields to append to registered ones', []);
        } catch (\Throwable $e) {
            var_dump($e);
            throw $e;
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $updatedOnly = $input->getOption('updated-only');
        $stateFrom = $input->getOption('state-from');
        $displayFieldList = $input->getOption('field');
        $additionalDisplayFieldList = $input->getOption('with-field');
        if (!empty($additionalDisplayFieldList)) {
            $displayFieldList = array_merge($displayFieldList, $additionalDisplayFieldList);
        }
        if (true === $updatedOnly && null === $stateFrom) {
            $this->symfonyStyle->error('--state-from is required in order to use --updated-only');

            return Command::INVALID;
        }

        $packageIterator = $this->createPackageIterator($stateFrom, $updatedOnly);

        /** @var array{name: string, path: string, state: string} $packageListInfos */
        $packageListInfos = [];
        foreach ($packageIterator as $package) {
            $packageListInfos[$package->name] = array_reduce(
                $displayFieldList,
                function (array $carry, string $field) use ($package): array {
                    switch ($field) {
                        case 'vendor':
                            $carry[$field] = explode('/', $package->name)[0];
                            break;
                        case 'name':
                            $carry[$field] = $package->name;
                            break;
                        case 'short_name':
                            $carry[$field] = explode('/', $package->name)[1];
                            break;
                        case 'path':
                            $carry[$field] = $package->path;
                            break;
                        case 'state':
                            $carry[$field] = $package->state->value;
                            break;
                    }

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
            $headers = array_map(fn ($field) => ucfirst($field), $displayFieldList);
            $this->symfonyStyle->table($headers, $packageListInfos);
        }

        return Command::SUCCESS;
    }

    /**
     * @param string|null $stateFrom
     * @param bool        $updatedOnly
     *
     * @return iterable<string, Package>
     */
    protected function createPackageIterator(?string $stateFrom, bool $updatedOnly): iterable
    {
        $packageIterator = $this->packageHelper->getPackages();
        if (null !== $stateFrom) {
            $packageIterator = new \CallbackFilterIterator(
                $packageIterator,
                function (Package $package) use ($stateFrom) {
                    $this->packageHelper->resolveState($package, $stateFrom);
                    // Return true to keep the item
                    return true;
                }
            );
        }
        if (true === $updatedOnly) {
            $packageIterator = new \CallbackFilterIterator(
                $packageIterator,
                fn(Package $package) => $package->state === PackageState::Updated
            );
        }

        return $packageIterator;
    }
}
