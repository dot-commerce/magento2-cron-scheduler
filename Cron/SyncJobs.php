<?php
/**
 * @author    Mudassar Iqbal <miqbal@dotcommerce.co>
 * @copyright Copyright (c) Dot Commerce
 * @license   MIT
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Cron;

use DotCommerce\CronScheduler\Model\FailedJobs\FailedJobsNotifier;
use DotCommerce\CronScheduler\Model\JobSynchronizer;
use Psr\Log\LoggerInterface;

class SyncJobs
{
    public function __construct(
        private readonly JobSynchronizer $jobSynchronizer,
        private readonly FailedJobsNotifier $failedJobsNotifier,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(): void
    {
        try {
            $this->jobSynchronizer->execute();
        } catch (\Throwable $e) {
            $this->logger->error(
                'Cron Scheduler: failed to synchronize cron jobs. ' . $e->getMessage(),
                ['exception' => $e]
            );
        }

        try {
            $this->failedJobsNotifier->execute();
        } catch (\Throwable $e) {
            $this->logger->error(
                'Cron Scheduler: failed to process failure notifications. ' . $e->getMessage(),
                ['exception' => $e]
            );
        }
    }
}
