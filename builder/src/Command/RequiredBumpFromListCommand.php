<?php
declare (strict_types=1);

namespace PhpJsonRpc\Builder\Command;

use ArrayIterator;
use MonorepoBuilderPrefix202304\Symfony\Component\Console\Command\Command;
use MonorepoBuilderPrefix202304\Symfony\Component\Console\Input\InputInterface;
use MonorepoBuilderPrefix202304\Symfony\Component\Console\Input\InputOption;
use MonorepoBuilderPrefix202304\Symfony\Component\Console\Output\OutputInterface;
use MonorepoBuilderPrefix202304\Symplify\PackageBuilder\Console\Command\AbstractSymplifyCommand;
use PhpJsonRpc\Builder\Helper\BumpHelper;
use PhpJsonRpc\Builder\Model\Changelog\BumpEnum;
use Symfony\Component\Console\Input\InputArgument;
use function sprintf;
use function ucfirst;

class RequiredBumpFromListCommand extends AbstractSymplifyCommand
{
    protected function configure()
    {
        $this->setName('version:required-bump')
            ->addArgument('bump', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'A bump')
            ->addOption('raw', null, InputOption::VALUE_NONE, 'Output required as raw string')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $bumpList = (array)$input->getArgument('bump');
        $isRawOutput = $input->getOption('raw');

        $requiredBump = BumpHelper::findRequiredIn(
            new ArrayIterator(
                array_map(static fn ($value) => BumpEnum::fromString($value), $bumpList)
            )
        );

        if ($isRawOutput) {
            $this->symfonyStyle->writeln($requiredBump->name);
        } else {
            $this->symfonyStyle->success(sprintf('%s bump required based on the provided list', $requiredBump === BumpEnum::none ? 'No' : ucfirst($requiredBump->name)));
        }

        return Command::SUCCESS;
    }
}
