<?php
/**
 * @copyright Copyright (c) DotCommerce
 * @license   Open Software License 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Cron;

use DotCommerce\CronScheduler\Model\JobSynchronizer;
use Psr\Log\LoggerInterface;

/**
 * Heartbeat cron that keeps the managed-job registry in sync with the
 * live cron configuration. Scheduled every minute via etc/crontab.xml.
 */
class SyncJobs
{
    public function __construct(
        private readonly JobSynchronizer $jobSynchronizer,
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
    }
}
