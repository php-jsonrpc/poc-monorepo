<?php
declare (strict_types=1);

namespace PhpJsonRpc\Builder\Command;

use Exception;
use MonorepoBuilderPrefix202304\Symfony\Component\Console\Input\InputInterface;
use MonorepoBuilderPrefix202304\Symfony\Component\Console\Output\OutputInterface;
use MonorepoBuilderPrefix202304\Symplify\PackageBuilder\Console\Command\AbstractSymplifyCommand;
use PhpJsonRpc\Builder\Helper\PackageHelper;
use PhpJsonRpc\Builder\Model\Package;
use PhpJsonRpc\Builder\Model\PackageState;
use MonorepoBuilderPrefix202304\Symfony\Component\Console\Command\Command;
use MonorepoBuilderPrefix202304\Symfony\Component\Console\Input\InputOption;
use function explode;
use function str_replace;

class ListPackagesCommand extends AbstractSymplifyCommand
{
    const FIELD_LIST = ['name', 'path', 'state', 'vendor', 'short_name', 'split_branch'];

    public function __construct(
        private readonly PackageHelper $packageHelper,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('packages:list')
            ->setDescription('Output list of known packages')
            ->addOption('state-from', null, InputOption::VALUE_REQUIRED, 'Resolve package state compared to another branch', null)
            ->addOption('updated-only', null, InputOption::VALUE_NONE, 'Keep only updated packages. Require --state-from option !')
            ->addOption('all-fields', null, InputOption::VALUE_NONE, 'Display all available fields. Default to "field" option value.')
            ->addOption('json', null, InputOption::VALUE_NONE, 'Output the list as JSON object')
            ->addOption('field', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Fields to display, vendor, name and path by default', ['name', 'path'])
            ->addOption('with-field', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'List of fields to append to registered ones', [])
            ->addOption(
                'split-branch-pattern',
                null,
                InputOption::VALUE_REQUIRED,
                <<<DOC
Split branch pattern
Generate the branch name to use when splitting macrorepo to package branches.
Parameters: 1=name, 2=vendor, 3=short_name
DOC,
                'split/%1$s/develop'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $updatedOnly = $input->getOption('updated-only');
        $allField = $input->getOption('all-fields');
        $stateFrom = $input->getOption('state-from');
        /* @var array<int, string> $displayFieldList */
        $displayFieldList = true === $allField ? self::FIELD_LIST : $input->getOption('field');
        $splitBranchPattern = $input->getOption('split-branch-pattern');

        $additionalDisplayFieldList = $input->getOption('with-field');
        if (false === $allField && false === empty($additionalDisplayFieldList)) {
            $displayFieldList = array_merge($displayFieldList, $additionalDisplayFieldList);
        }
        if (true === $updatedOnly && null === $stateFrom) {
            $this->symfonyStyle->error('--state-from is required in order to use --updated-only');

            return Command::INVALID;
        }

        $packageIterator = $this->createPackageIterator($stateFrom, $updatedOnly);

        $packageListInfos = [];
        foreach ($packageIterator as $package) {
            [$vendor, $shortName] = explode('/', $package->name);
            $packageListInfos[$package->name] = array_reduce(
                $displayFieldList,
                function (array $carry, string $field) use ($package, $vendor, $shortName, $splitBranchPattern): array {
                    $carry[$field] = match ($field) {
                        'vendor' => $vendor,
                        'name' => $package->name,
                        'short_name' => $shortName,
                        'path' => $package->path,
                        'state' => $package->state->value,
                        'split_branch' => sprintf(
                            $splitBranchPattern,
                            $package->name,
                            $vendor,
                            $shortName
                        ),
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

    /**
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
