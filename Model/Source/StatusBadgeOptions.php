<?php
/**
 * @copyright Copyright (c) DotCommerce
 * @license   Open Software License 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Escaper;

/**
 * Color-coded status labels used for rendering the grid Status column.
 */
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
                'value' => Status::ENABLED->value,
                'label' => '<span class="grid-severity-notice">'
                    . $this->escaper->escapeHtml((string) Status::ENABLED->label()) . '</span>',
            ],
            [
                'value' => Status::DISABLED->value,
                'label' => '<span class="grid-severity-critical">'
                    . $this->escaper->escapeHtml((string) Status::DISABLED->label()) . '</span>',
            ],
        ];
    }
}
