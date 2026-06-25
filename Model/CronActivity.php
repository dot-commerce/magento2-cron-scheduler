<?php
/**
 * @author    Mudassar Iqbal <miqbal@dotcommerce.co>
 * @copyright Copyright (c) Dot Commerce
 * @license   MIT
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Model;

use Magento\Cron\Model\ResourceModel\Schedule as ScheduleResource;
use Magento\Cron\Model\Schedule;
use Magento\Framework\DB\Sql\Expression;

class CronActivity
{
    public function __construct(
        private readonly ScheduleResource $scheduleResource
    ) {
    }

    public function getLastHeartbeatAt(): ?string
    {
        $connection = $this->scheduleResource->getConnection();

        $select = $connection->select()
            ->from($this->scheduleResource->getMainTable(), [new Expression('MAX(finished_at)')])
            ->where('job_code = ?', JobSynchronizer::SELF_JOB_CODE)
            ->where('status = ?', Schedule::STATUS_SUCCESS);

        $value = $connection->fetchOne($select);

        return $value !== false && $value !== null && $value !== '' ? (string) $value : null;
    }
}
