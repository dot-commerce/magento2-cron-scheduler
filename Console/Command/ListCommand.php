<?php
/**
 * @author    Mudassar Iqbal <miqbal@dotcommerce.co>
 * @copyright Copyright (c) Dot Commerce
 * @license   MIT
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Console\Command;

use DotCommerce\CronScheduler\Api\Data\JobInterface;
use DotCommerce\CronScheduler\Model\ResourceModel\Job\CollectionFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends Command
{
    private const OPT_STATUS = 'status';
    private const OPT_GROUP = 'group';

    public function __construct(
        private readonly CollectionFactory $collectionFactory,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('dc:cron:list')
            ->setDescription('List managed cron jobs.')
            ->addOption(self::OPT_STATUS, null, InputOption::VALUE_REQUIRED, 'Filter by status: enabled or disabled')
            ->addOption(self::OPT_GROUP, null, InputOption::VALUE_REQUIRED, 'Filter by cron group');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $collection = $this->collectionFactory->create();
        $collection->setOrder(JobInterface::JOB_CODE, 'ASC');

        $status = $input->getOption(self::OPT_STATUS);
        if ($status !== null) {
            $normalized = strtolower((string) $status);
            if (!in_array($normalized, ['enabled', 'disabled'], true)) {
                $output->writeln('<error>--status must be "enabled" or "disabled".</error>');

                return self::FAILURE;
            }
            $collection->addFieldToFilter(
                JobInterface::STATUS,
                $normalized === 'enabled' ? JobInterface::STATUS_ENABLED : JobInterface::STATUS_DISABLED
            );
        }

        $group = $input->getOption(self::OPT_GROUP);
        if ($group !== null) {
            $collection->addFieldToFilter(JobInterface::GROUP_CODE, (string) $group);
        }

        if ($collection->getSize() === 0) {
            $output->writeln('<comment>No managed cron jobs found.</comment>');

            return self::SUCCESS;
        }

        $table = new Table($output);
        $table->setHeaders(['Job Code', 'Group', 'Status', 'Schedule', 'Modified Schedule']);

        /** @var JobInterface $job */
        foreach ($collection as $job) {
            $table->addRow([
                (string) $job->getJobCode(),
                (string) $job->getGroupCode(),
                $job->isEnabled() ? 'Enabled' : 'Disabled',
                (string) $job->getSchedule(),
                (string) $job->getModifiedSchedule(),
            ]);
        }

        $table->render();

        return self::SUCCESS;
    }
}
