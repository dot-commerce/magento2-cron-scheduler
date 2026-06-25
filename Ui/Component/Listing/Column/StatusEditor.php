<?php
/**
 * @author    Mudassar Iqbal <miqbal@dotcommerce.co>
 * @copyright Copyright (c) Dot Commerce
 * @license   MIT
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Ui\Component\Listing\Column;

use DotCommerce\CronScheduler\Model\Source\StatusOptions;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

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
