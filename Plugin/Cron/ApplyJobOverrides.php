<?php
/**
 * @author    Mudassar Iqbal <miqbal@dotcommerce.co>
 * @copyright Copyright (c) Dot Commerce
 * @license   MIT
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Plugin\Cron;

use DotCommerce\CronScheduler\Api\Data\JobInterface;
use DotCommerce\CronScheduler\Model\ResourceModel\Job\Collection;
use DotCommerce\CronScheduler\Model\ResourceModel\Job\CollectionFactory;
use Magento\Cron\Model\Config;
use Psr\Log\LoggerInterface;

/**
 * Applies the admin's overrides to the live cron configuration:
 *  - a disabled job is removed entirely, so Magento never schedules it
 *  - a job with a modified schedule runs on the admin-chosen expression.
 *
 * Acts on {@see Config::getJobs}, the source of truth the cron scheduler reads.
 */
class ApplyJobOverrides
{
    /**
     * @var array<string, JobInterface>|null
     */
    private ?array $managedJobs = null;

    public function __construct(
        private readonly CollectionFactory $collectionFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    public function afterGetJobs(Config $subject, array $result): array
    {
        $managedJobs = $this->getManagedJobs();

        if (!$managedJobs) {
            return $result;
        }

        foreach ($result as &$groupJobs) {
            foreach ($groupJobs as $jobCode => &$job) {
                $code = $job['name'] ?? $jobCode;

                if (!isset($managedJobs[$code])) {
                    continue;
                }

                $managedJob = $managedJobs[$code];

                if (!$managedJob->isEnabled()) {
                    unset($groupJobs[$jobCode]);
                    continue;
                }

                $modifiedSchedule = $managedJob->getModifiedSchedule();

                if ($modifiedSchedule !== null && $modifiedSchedule !== ($job['schedule'] ?? null)) {
                    $job['schedule'] = $modifiedSchedule;
                    // A fixed schedule overrides any dynamic config-path schedule.
                    unset($job['config_path']);
                }
            }
            unset($job);
        }
        unset($groupJobs);

        return $result;
    }

    /**
     * Loads the jobs the admin has actually changed (disabled, or given a modified schedule)
     *
     * @return array<string, JobInterface>
     */
    private function getManagedJobs(): array
    {
        if ($this->managedJobs !== null) {
            return $this->managedJobs;
        }

        $this->managedJobs = [];

        try {
            /** @var Collection $collection */
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter(
                [JobInterface::STATUS, JobInterface::MODIFIED_SCHEDULE],
                [
                    ['eq' => JobInterface::STATUS_DISABLED],
                    ['notnull' => true],
                ]
            );

            /** @var JobInterface $job */
            foreach ($collection as $job) {
                $this->managedJobs[(string) $job->getJobCode()] = $job;
            }
        } catch (\Throwable $e) {
            // Table not ready or DB unavailable: fall back to the unmodified cron config.
            $this->logger->warning(
                'Cron Scheduler: could not load job overrides, using default schedule. ' . $e->getMessage()
            );
        }

        return $this->managedJobs;
    }
}
