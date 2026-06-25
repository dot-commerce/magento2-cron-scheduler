<?php
/**
 * @author    Mudassar Iqbal <miqbal@dotcommerce.co>
 * @copyright Copyright (c) Dot Commerce
 * @license   MIT
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Ui\Component\Listing\Column;

use Magento\Cron\Model\Schedule;
use Magento\Ui\Component\Listing\Columns\Column;

class ScheduleStatus extends Column
{
    private const SEVERITY = [
        Schedule::STATUS_SUCCESS => 'notice',
        Schedule::STATUS_PENDING => 'minor',
        Schedule::STATUS_RUNNING => 'minor',
        Schedule::STATUS_MISSED => 'critical',
        Schedule::STATUS_ERROR => 'critical',
    ];

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
            $status = (string) ($item[$fieldName] ?? '');

            if ($status === '') {
                continue;
            }

            $severity = self::SEVERITY[$status] ?? 'minor';
            $item[$fieldName] = sprintf(
                '<span class="grid-severity-%s"><span>%s</span></span>',
                $severity,
                ucfirst($status)
            );
        }
        unset($item);

        return $dataSource;
    }
}
