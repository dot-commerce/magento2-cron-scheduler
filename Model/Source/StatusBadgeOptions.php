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
use Magento\Framework\Escaper;

class StatusBadgeOptions implements OptionSourceInterface
{
    public function __construct(
        private readonly Escaper $escaper
    ) {
    }

    public function toOptionArray(): array
    {
        return [
            [
                'value' => JobInterface::STATUS_ENABLED,
                'label' => '<span class="grid-severity-notice">'
                    . $this->escaper->escapeHtml((string) __('Enabled')) . '</span>',
            ],
            [
                'value' => JobInterface::STATUS_DISABLED,
                'label' => '<span class="grid-severity-critical">'
                    . $this->escaper->escapeHtml((string) __('Disabled')) . '</span>',
            ],
        ];
    }
}
