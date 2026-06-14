<?php
/**
 * @copyright Copyright (c) DotCommerce
 * @license   Open Software License 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Ui\DataProvider;

use DotCommerce\CronScheduler\Model\ResourceModel\Job\Collection;
use DotCommerce\CronScheduler\Model\ResourceModel\Job\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

class JobDataProvider extends AbstractDataProvider
{
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }

    public function getCollection(): Collection
    {
        /** @var Collection $collection */
        $collection = $this->collection;

        return $collection;
    }
}
