<?php
/**
 * @copyright Copyright (c) DotCommerce
 * @license   Open Software License 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Model\ResourceModel\Schedule;

use Magento\Framework\App\ResourceConnection;

/**
 * Purges rows from Magento's native cron_schedule table for job codes
 * that no longer exist in any module's cron configuration.
 */
class DeleteOrphanedSchedules
{
    private const CRON_SCHEDULE_TABLE = 'cron_schedule';

    public function __construct(
        private readonly ResourceConnection $resourceConnection
    ) {
    }

    /**
     * @param string[] $jobCodes
     */
    public function execute(array $jobCodes): void
    {
        if (!$jobCodes) {
            return;
        }

        $connection = $this->resourceConnection->getConnection();
        $connection->delete(
            $this->resourceConnection->getTableName(self::CRON_SCHEDULE_TABLE),
            ['job_code IN (?)' => $jobCodes]
        );
    }
}
