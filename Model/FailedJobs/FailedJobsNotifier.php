<?php
/**
 * @author    Mudassar Iqbal <miqbal@dotcommerce.co>
 * @copyright Copyright (c) Dot Commerce
 * @license   MIT
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Model\FailedJobs;

use DotCommerce\CronScheduler\Api\JobRepositoryInterface;
use DotCommerce\CronScheduler\Model\Config\EmailNotificationConfig;
use DotCommerce\CronScheduler\Model\FailureNotification;
use DotCommerce\CronScheduler\Model\FailureNotificationFactory;
use DotCommerce\CronScheduler\Model\ResourceModel\FailureNotification as FailureNotificationResource;
use DotCommerce\CronScheduler\Model\ResourceModel\FailureNotification\CollectionFactory as StateCollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

class FailedJobsNotifier
{
    public function __construct(
        private readonly EmailNotificationConfig $config,
        private readonly FailedScheduleReader $reader,
        private readonly EmailSender $emailSender,
        private readonly JobRepositoryInterface $jobRepository,
        private readonly FailureNotificationFactory $stateFactory,
        private readonly FailureNotificationResource $stateResource,
        private readonly StateCollectionFactory $stateCollectionFactory,
        private readonly DateTime $dateTime,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(): void
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        $failures = $this->reader->getLatestErrorPerJob();

        if (!$failures) {
            return;
        }

        $interval = $this->config->getIntervalSeconds();
        $now = $this->dateTime->gmtTimestamp();
        $states = $this->loadStatesByCode();

        foreach ($failures as $failure) {
            $jobCode = (string) ($failure['job_code'] ?? '');

            if ($jobCode === '' || !$this->isManagedJob($jobCode)) {
                continue;
            }

            $failureTs = $this->toUtcTimestamp((string) ($failure['executed_at'] ?? ''));

            if ($failureTs === null) {
                continue;
            }

            $state = $states[$jobCode] ?? null;
            $lastNotifiedTs = $this->toUtcTimestamp((string) ($state?->getLastNotifiedAt() ?? ''));

            // Skip if there is no failure newer than the last one we alerted on.
            if ($lastNotifiedTs !== null && $failureTs <= $lastNotifiedTs) {
                continue;
            }

            // Throttle: at most one alert per interval per job.
            if ($lastNotifiedTs !== null && ($now - $lastNotifiedTs) < $interval) {
                continue;
            }

            try {
                $this->emailSender->send($failure);
            } catch (\Throwable $e) {
                $this->logger->error(
                    'Cron Scheduler: failed to send failure notification for "' . $jobCode . '". ' . $e->getMessage(),
                    ['exception' => $e]
                );
                // Leave state untouched so the alert is retried on the next tick.
                continue;
            }

            $this->persistState($state, $jobCode, (string) ($failure['executed_at'] ?? ''));
        }
    }

    /**
     * @return array<string, FailureNotification>
     */
    private function loadStatesByCode(): array
    {
        $states = [];

        /** @var FailureNotification $state */
        foreach ($this->stateCollectionFactory->create() as $state) {
            $states[(string) $state->getJobCode()] = $state;
        }

        return $states;
    }

    private function isManagedJob(string $jobCode): bool
    {
        try {
            $this->jobRepository->getByCode($jobCode);

            return true;
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }

    private function persistState(?FailureNotification $state, string $jobCode, string $failureAt): void
    {
        if ($state === null) {
            /** @var FailureNotification $state */
            $state = $this->stateFactory->create();
            $state->setJobCode($jobCode);
        }

        $state->setLastFailureAt($failureAt !== '' ? $failureAt : null);
        $state->setLastNotifiedAt($this->dateTime->gmtDate());

        $this->stateResource->save($state);
    }

    private function toUtcTimestamp(string $value): ?int
    {
        if ($value === '' || $value === '0000-00-00 00:00:00') {
            return null;
        }

        try {
            return (new \DateTime($value, new \DateTimeZone('UTC')))->getTimestamp();
        } catch (\Exception $e) {
            return null;
        }
    }
}
