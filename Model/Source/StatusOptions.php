<?php
/**
 * @copyright Copyright (c) DotCommerce
 * @license   Open Software License 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Plain status options used by the grid filter and the inline editor.
 */
class StatusOptions implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => Status::ENABLED->value, 'label' => Status::ENABLED->label()],
            ['value' => Status::DISABLED->value, 'label' => Status::DISABLED->label()],
        ];
    }
}
