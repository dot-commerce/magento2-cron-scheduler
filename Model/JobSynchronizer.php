<?php
/**
 * @copyright Copyright (c) DotCommerce
 * @license   Open Software License 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Model;

use DotCommerce\CronScheduler\Api\Data\JobInterface;
use DotCommerce\CronScheduler\Api\JobRepositoryInterface;
use DotCommerce\CronScheduler\Model\ResourceModel\Job\Collection;
use DotCommerce\CronScheduler\Model\ResourceModel\Job\CollectionFactory;
use DotCommerce\CronScheduler\Model\ResourceModel\Schedule\DeleteOrphanedSchedules;
use DotCommerce\CronScheduler\Model\Source\Status;
use Magento\Cron\Model\Config\Data as CronConfigData;

/**
 * Reconciles the managed-job registry with Magento's raw cron configuration:
 *  - inserts jobs newly declared in crontab.xml,
 *  - refreshes the stored original schedule when a module changes it,
 *  - removes jobs that no longer exist and purges their cron_schedule rows.
 *
 * It reads from {@see CronConfigData} (the raw merged config) on purpose, so that
 * this module's own runtime overrides do not feed back into the registry.
 */
class JobSynchronizer
{
    /**
     * This module's heartbeat job - never tracked in the managed registry.
     */
    public const SELF_JOB_CODE = 'dotcommerce_cron_sync';

    /**
     * Attributes a cron job declaration must define to be manageable.
     *
     * @see Magento_Cron:etc/crontab.xsd
     */
    private const REQUIRED_ATTRIBUTES = ['name', 'instance', 'method'];

    private const DELETE_BATCH_SIZE = 100;

    public function __construct(
        private readonly CronConfigData $cronConfigData,
        private readonly JobRepositoryInterface $jobRepository,
        private readonly JobFactory $jobFactory,
        private readonly CollectionFactory $collectionFactory,
        private readonly DeleteOrphanedSchedules $deleteOrphanedSchedules
    ) {
    }

    public function execute(): void
    {
        $configuredJobs = $this->cronConfigData->getJobs();
        $storedJobs = $this->getStoredJobsByCode();

        $seenCodes = [];

        foreach ($configuredJobs as $groupId => $jobs) {
            foreach ($jobs as $job) {
                $code = $job['name'] ?? null;

                if ($code === null || $code === self::SELF_JOB_CODE) {
                    continue;
                }

                if (!$this->isValid($job)) {
                    continue;
                }

                $seenCodes[$code] = true;

                if (isset($storedJobs[$code])) {
                    $this->refreshStoredJob($storedJobs[$code], (string) $groupId, $job);
                } else {
                    $this->createStoredJob((string) $groupId, $job);
                }
            }
        }

        $this->removeObsoleteJobs($storedJobs, $seenCodes);
    }

    /**
     * @return array<string, JobInterface>
     */
    private function getStoredJobsByCode(): array
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $stored = [];

        /** @var JobInterface $job */
        foreach ($collection as $job) {
            $stored[(string) $job->getJobCode()] = $job;
        }

        return $stored;
    }

    private function createStoredJob(string $groupId, array $job): void
    {
        /** @var JobInterface $model */
        $model = $this->jobFactory->create();
        $model->setJobCode((string) $job['name']);
        $model->setGroupCode($groupId);
        $model->setInstance((string) $job['instance']);
        $model->setMethod((string) $job['method']);
        $model->setSchedule(isset($job['schedule']) ? (string) $job['schedule'] : null);
        $model->setStatus(Status::ENABLED->value);

        $this->jobRepository->save($model);
    }

    /**
     * Keeps the stored original schedule (and group) in sync with the source config,
     * without touching the admin's overrides (modified_schedule / status).
     */
    private function refreshStoredJob(JobInterface $stored, string $groupId, array $job): void
    {
        $configSchedule = isset($job['schedule']) ? (string) $job['schedule'] : null;
        $changed = false;

        if ($stored->getSchedule() !== $configSchedule) {
            $stored->setSchedule($configSchedule);
            $changed = true;
        }

        if ($stored->getGroupCode() !== $groupId) {
            $stored->setGroupCode($groupId);
            $changed = true;
        }

        if ($changed) {
            $this->jobRepository->save($stored);
        }
    }

    /**
     * @param array<string, JobInterface> $storedJobs
     * @param array<string, bool> $seenCodes
     */
    private function removeObsoleteJobs(array $storedJobs, array $seenCodes): void
    {
        $codesToPurge = [];

        foreach ($storedJobs as $code => $job) {
            if (isset($seenCodes[$code])) {
                continue;
            }

            $this->jobRepository->delete($job);
            $codesToPurge[] = $code;

            if (count($codesToPurge) >= self::DELETE_BATCH_SIZE) {
                $this->deleteOrphanedSchedules->execute($codesToPurge);
                $codesToPurge = [];
            }
        }

        $this->deleteOrphanedSchedules->execute($codesToPurge);
    }

    private function isValid(array $job): bool
    {
        foreach (self::REQUIRED_ATTRIBUTES as $attribute) {
            if (!isset($job[$attribute]) || $job[$attribute] === '') {
                return false;
            }
        }

        return true;
    }
}
