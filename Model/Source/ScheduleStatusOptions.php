<?php
/**
 * @author    Mudassar Iqbal <miqbal@dotcommerce.co>
 * @copyright Copyright (c) Dot Commerce
 * @license   MIT
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Model\Source;

use Magento\Cron\Model\Schedule;
use Magento\Framework\Data\OptionSourceInterface;

class ScheduleStatusOptions implements OptionSourceInterface
{
    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => Schedule::STATUS_PENDING, 'label' => __('Pending')],
            ['value' => Schedule::STATUS_RUNNING, 'label' => __('Running')],
            ['value' => Schedule::STATUS_SUCCESS, 'label' => __('Success')],
            ['value' => Schedule::STATUS_MISSED, 'label' => __('Missed')],
            ['value' => Schedule::STATUS_ERROR, 'label' => __('Error')],
        ];
    }
}
