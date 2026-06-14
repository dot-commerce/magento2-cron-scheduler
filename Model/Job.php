<?php
/**
 * @copyright Copyright (c) DotCommerce
 * @license   Open Software License 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Model;

use DotCommerce\CronScheduler\Api\Data\JobInterface;
use DotCommerce\CronScheduler\Model\ResourceModel\Job as JobResource;
use DotCommerce\CronScheduler\Model\Source\Status;
use Magento\Framework\Model\AbstractModel;

class Job extends AbstractModel implements JobInterface
{
    protected function _construct(): void
    {
        $this->_init(JobResource::class);
    }

    public function getEntityId(): ?int
    {
        $value = $this->getData(self::ENTITY_ID);

        return $value === null ? null : (int) $value;
    }

    public function setEntityId($entityId): self
    {
        return $this->setData(self::ENTITY_ID, $entityId === null ? null : (int) $entityId);
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

    public function getGroupCode(): ?string
    {
        $value = $this->getData(self::GROUP_CODE);

        return $value === null ? null : (string) $value;
    }

    public function setGroupCode(string $groupCode): self
    {
        return $this->setData(self::GROUP_CODE, $groupCode);
    }

    public function getInstance(): ?string
    {
        $value = $this->getData(self::INSTANCE);

        return $value === null ? null : (string) $value;
    }

    public function setInstance(string $instance): self
    {
        return $this->setData(self::INSTANCE, $instance);
    }

    public function getMethod(): ?string
    {
        $value = $this->getData(self::METHOD);

        return $value === null ? null : (string) $value;
    }

    public function setMethod(string $method): self
    {
        return $this->setData(self::METHOD, $method);
    }

    public function getSchedule(): ?string
    {
        $value = $this->getData(self::SCHEDULE);

        return $value === null ? null : (string) $value;
    }

    public function setSchedule(?string $schedule): self
    {
        return $this->setData(self::SCHEDULE, $schedule);
    }

    public function getModifiedSchedule(): ?string
    {
        $value = $this->getData(self::MODIFIED_SCHEDULE);

        return $value === null || $value === '' ? null : (string) $value;
    }

    public function setModifiedSchedule(?string $modifiedSchedule): self
    {
        return $this->setData(self::MODIFIED_SCHEDULE, $modifiedSchedule);
    }

    public function isEnabled(): bool
    {
        return $this->getStatus() === Status::ENABLED->value;
    }

    public function getStatus(): int
    {
        return (int) $this->getData(self::STATUS);
    }

    public function setStatus(int $status): self
    {
        return $this->setData(self::STATUS, $status);
    }
}
