<?php
/**
 * @author    Mudassar Iqbal <miqbal@dotcommerce.co>
 * @copyright Copyright (c) Dot Commerce
 * @license   MIT
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Model\FailedJobs;

use Magento\Cron\Model\ResourceModel\Schedule as ScheduleResource;
use Magento\Cron\Model\Schedule;
use Magento\Framework\DB\Sql\Expression;

class FailedScheduleReader
{
    public function __construct(
        private readonly ScheduleResource $scheduleResource
    ) {
    }

    /**
     * @return array<int, array{job_code: string, executed_at: ?string, messages: ?string}>
     */
    public function getLatestErrorPerJob(): array
    {
        $connection = $this->scheduleResource->getConnection();
        $table = $this->scheduleResource->getMainTable();

        $latest = $connection->select()
            ->from($table, ['job_code', 'max_id' => new Expression('MAX(schedule_id)')])
            ->where('status = ?', Schedule::STATUS_ERROR)
            ->group('job_code');

        $select = $connection->select()
            ->from(['cs' => $table], ['job_code', 'executed_at', 'messages'])
            ->join(['latest' => $latest], 'cs.schedule_id = latest.max_id', []);

        return $connection->fetchAll($select);
    }
}
