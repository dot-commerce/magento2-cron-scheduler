<?php
/**
 * @author    Mudassar Iqbal <miqbal@dotcommerce.co>
 * @copyright Copyright (c) Dot Commerce
 * @license   MIT
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class FailureNotification extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('dc_cronscheduler_failure_notification', 'entity_id');
    }
}
