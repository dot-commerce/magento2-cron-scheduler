<?php
/**
 * @copyright Copyright (c) DotCommerce
 * @license   Open Software License 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Ui\Component\Listing\Column;

use DotCommerce\CronScheduler\Model\Source\StatusOptions;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Status column whose cell renders the colour-coded badge (via the column's
 * `options`) while the inline editor dropdown uses plain text labels.
 */
class StatusEditor extends Column
{
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private readonly StatusOptions $statusOptions,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepare(): void
    {
        $config = $this->getData('config');
        $config['editor']['options'] = $this->statusOptions->toOptionArray();
        $this->setData('config', $config);

        parent::prepare();
    }
}
