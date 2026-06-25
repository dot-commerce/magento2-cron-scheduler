<?php
/**
 * @author    Mudassar Iqbal <miqbal@dotcommerce.co>
 * @copyright Copyright (c) Dot Commerce
 * @license   MIT
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Model;

use DotCommerce\CronScheduler\Api\Data\JobInterface;
use Magento\Cron\Model\ResourceModel\Schedule as ScheduleResource;
use Magento\Cron\Model\Schedule;
use Magento\Cron\Model\ScheduleFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class JobRunner
{
    public function __construct(
        private readonly ScheduleFactory $scheduleFactory,
        private readonly ScheduleResource $scheduleResource,
        private readonly ObjectManagerInterface $objectManager,
        private readonly DateTime $dateTime
    ) {
    }

    /**
     * @return string Human-readable profiling summary of the run.
     * @throws \Throwable Re-thrown after the schedule row is marked as error.
     */
    public function run(JobInterface $job): string
    {
        $instanceName = (string) $job->getInstance();
        $method = (string) $job->getMethod();

        $schedule = $this->createRunningSchedule((string) $job->getJobCode());

        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        try {
            $model = $this->objectManager->create($instanceName);

            if (!method_exists($model, $method)) {
                throw new \RuntimeException(
                    sprintf('Cron callback "%s::%s" does not exist.', $instanceName, $method)
                );
            }

            $model->{$method}($schedule);

            $profile = $this->profile($startTime, $startMemory);
            $this->finishSchedule($schedule, Schedule::STATUS_SUCCESS, $profile);

            return $profile;
        } catch (\Throwable $e) {
            $profile = $this->profile($startTime, $startMemory);
            $this->finishSchedule($schedule, Schedule::STATUS_ERROR, $e->getMessage() . ' (' . $profile . ')');

            throw $e;
        }
    }

    private function createRunningSchedule(string $jobCode): Schedule
    {
        $now = $this->now();

        /** @var Schedule $schedule */
        $schedule = $this->scheduleFactory->create();
        $schedule->setJobCode($jobCode)
            ->setStatus(Schedule::STATUS_RUNNING)
            ->setCreatedAt($now)
            ->setScheduledAt($now)
            ->setExecutedAt($now);

        $this->scheduleResource->save($schedule);

        return $schedule;
    }

    private function finishSchedule(Schedule $schedule, string $status, string $messages): void
    {
        $schedule->setStatus($status)
            ->setMessages($messages)
            ->setFinishedAt($this->now());

        $this->scheduleResource->save($schedule);
    }

    private function profile(float $startTime, int $startMemory): string
    {
        $elapsed = microtime(true) - $startTime;
        $memoryMb = max(0.0, (memory_get_usage(true) - $startMemory) / 1024 / 1024);

        return sprintf('Ran in %.2Fs, memory %.2F MB', $elapsed, $memoryMb);
    }

    private function now(): string
    {
        return date('Y-m-d H:i:s', $this->dateTime->gmtTimestamp());
    }
}
