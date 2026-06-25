<?php
/**
 * @author    Mudassar Iqbal <miqbal@dotcommerce.co>
 * @copyright Copyright (c) Dot Commerce
 * @license   MIT
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Ui\Component\Listing\Column;

use DotCommerce\CronScheduler\Api\Data\JobInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Per-row actions for the Cron Jobs List
 */
class JobActions extends Column
{
    private const URL_PATH_RUN = 'dc_cronscheduler/jobs/run';

    private const URL_PATH_SCHEDULE = 'dc_cronscheduler/jobs/schedule';

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private readonly UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @inheritDoc
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        $fieldName = $this->getData('name');

        foreach ($dataSource['data']['items'] as &$item) {
            $id = $item[JobInterface::ENTITY_ID] ?? null;

            if ($id === null) {
                continue;
            }

            $item[$fieldName]['run'] = [
                'href' => $this->urlBuilder->getUrl(
                    self::URL_PATH_RUN,
                    [JobInterface::ENTITY_ID => $id]
                ),
                'label' => __('Run Job'),
            ];

            $item[$fieldName]['schedule'] = [
                'href' => $this->urlBuilder->getUrl(
                    self::URL_PATH_SCHEDULE,
                    [JobInterface::ENTITY_ID => $id]
                ),
                'label' => __('Schedule Now'),
            ];
        }
        unset($item);

        return $dataSource;
    }
}
