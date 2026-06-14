<?php
/**
 * @copyright Copyright (c) DotCommerce
 * @license   Open Software License 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Model\ResourceModel\Job;

use DotCommerce\CronScheduler\Api\Data\JobInterface;
use DotCommerce\CronScheduler\Model\Job as JobModel;
use DotCommerce\CronScheduler\Model\ResourceModel\Job as JobResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = JobInterface::ENTITY_ID;

    protected function _construct(): void
    {
        $this->_init(JobModel::class, JobResource::class);
    }
}
