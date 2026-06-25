<?php
/**
 * @author    Mudassar Iqbal <miqbal@dotcommerce.co>
 * @copyright Copyright (c) Dot Commerce
 * @license   MIT
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Model\Source;

use DotCommerce\CronScheduler\Api\Data\JobInterface;
use Magento\Framework\Data\OptionSourceInterface;

class StatusOptions implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => JobInterface::STATUS_ENABLED, 'label' => __('Enabled')],
            ['value' => JobInterface::STATUS_DISABLED, 'label' => __('Disabled')],
        ];
    }
}
