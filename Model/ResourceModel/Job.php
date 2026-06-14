<?php
/**
 * @copyright Copyright (c) DotCommerce
 * @license   Open Software License 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Model\ResourceModel;

use DotCommerce\CronScheduler\Api\Data\JobInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Job extends AbstractDb
{
    public const TABLE_NAME = 'dc_cronscheduler_job';

    protected function _construct(): void
    {
        $this->_init(self::TABLE_NAME, JobInterface::ENTITY_ID);
    }
}
