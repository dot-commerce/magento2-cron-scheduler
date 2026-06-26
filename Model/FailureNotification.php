<?php
/**
 * @author    Mudassar Iqbal <miqbal@dotcommerce.co>
 * @copyright Copyright (c) Dot Commerce
 * @license   MIT
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Model;

use DotCommerce\CronScheduler\Model\ResourceModel\FailureNotification as FailureNotificationResource;
use Magento\Framework\Model\AbstractModel;

class FailureNotification extends AbstractModel
{
    public const JOB_CODE = 'job_code';
    public const LAST_FAILURE_AT = 'last_failure_at';
    public const LAST_NOTIFIED_AT = 'last_notified_at';

    protected function _construct(): void
    {
        $this->_init(FailureNotificationResource::class);
    }

    public function getJobCode(): ?string
    {
        $value = $this->getData(self::JOB_CODE);

        return $value === null ? null : (string) $value;
    }

    public function setJobCode(string $jobCode): self
    {
        return $this->setData(self::JOB_CODE, $jobCode);
    }

    public function getLastFailureAt(): ?string
    {
        $value = $this->getData(self::LAST_FAILURE_AT);

        return $value === null ? null : (string) $value;
    }

    public function setLastFailureAt(?string $lastFailureAt): self
    {
        return $this->setData(self::LAST_FAILURE_AT, $lastFailureAt);
    }

    public function getLastNotifiedAt(): ?string
    {
        $value = $this->getData(self::LAST_NOTIFIED_AT);

        return $value === null ? null : (string) $value;
    }

    public function setLastNotifiedAt(?string $lastNotifiedAt): self
    {
        return $this->setData(self::LAST_NOTIFIED_AT, $lastNotifiedAt);
    }
}
