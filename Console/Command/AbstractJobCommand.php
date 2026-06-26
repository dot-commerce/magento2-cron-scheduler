<?php
/**
 * @author    Mudassar Iqbal <miqbal@dotcommerce.co>
 * @copyright Copyright (c) Dot Commerce
 * @license   MIT
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Console\Command;

use DotCommerce\CronScheduler\Api\Data\JobInterface;
use DotCommerce\CronScheduler\Api\JobRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractJobCommand extends Command
{
    protected const ARG_JOB_CODE = 'job_code';

    public function __construct(
        protected readonly JobRepositoryInterface $jobRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(self::ARG_JOB_CODE, InputArgument::REQUIRED, 'The cron job code');
    }

    protected function getJob(string $jobCode, OutputInterface $output): ?JobInterface
    {
        try {
            return $this->jobRepository->getByCode($jobCode);
        } catch (NoSuchEntityException $e) {
            $output->writeln(sprintf('<error>No managed cron job found with code "%s".</error>', $jobCode));

            return null;
        }
    }
}
