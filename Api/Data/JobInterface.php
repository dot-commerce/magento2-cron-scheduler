<?php
/**
 * @copyright Copyright (c) DotCommerce
 * @license   Open Software License 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Api\Data;

/**
 * A single managed cron job, mirrored from Magento's cron configuration.
 *
 * @api
 */
interface JobInterface
{
    public const ENTITY_ID          = 'entity_id';
    public const JOB_CODE           = 'job_code';
    public const GROUP_CODE         = 'group_code';
    public const INSTANCE           = 'instance';
    public const METHOD             = 'method';
    public const SCHEDULE           = 'schedule';
    public const MODIFIED_SCHEDULE  = 'modified_schedule';
    public const STATUS             = 'status';

    public function getEntityId(): ?int;

    public function setEntityId(?int $entityId): self;

    public function getJobCode(): ?string;

    public function setJobCode(string $jobCode): self;

    public function getGroupCode(): ?string;

    public function setGroupCode(string $groupCode): self;

    public function getInstance(): ?string;

    public function setInstance(string $instance): self;

    public function getMethod(): ?string;

    public function setMethod(string $method): self;

    public function getSchedule(): ?string;

    public function setSchedule(?string $schedule): self;

    public function getModifiedSchedule(): ?string;

    public function setModifiedSchedule(?string $modifiedSchedule): self;

    public function isEnabled(): bool;

    public function getStatus(): int;

    public function setStatus(int $status): self;
}
