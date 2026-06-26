<?php
/**
 * @author    Mudassar Iqbal <miqbal@dotcommerce.co>
 * @copyright Copyright (c) Dot Commerce
 * @license   MIT
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Console\Command;

use DotCommerce\CronScheduler\Api\Data\JobInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EnableCommand extends AbstractJobCommand
{
    protected function configure(): void
    {
        $this->setName('dc:cron:enable')
            ->setDescription('Enable a managed cron job so Magento schedules it again.');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $jobCode = (string) $input->getArgument(self::ARG_JOB_CODE);
        $job = $this->getJob($jobCode, $output);

        if ($job === null) {
            return self::FAILURE;
        }

        if ($job->isEnabled()) {
            $output->writeln(sprintf('<info>Job "%s" is already enabled.</info>', $jobCode));

            return self::SUCCESS;
        }

        $job->setStatus(JobInterface::STATUS_ENABLED);
        $this->jobRepository->save($job);

        $output->writeln(sprintf('<info>Job "%s" has been enabled.</info>', $jobCode));

        return self::SUCCESS;
    }
}
