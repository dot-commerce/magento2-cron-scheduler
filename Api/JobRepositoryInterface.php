<?php
/**
 * @copyright Copyright (c) DotCommerce
 * @license   Open Software License 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Api;

use DotCommerce\CronScheduler\Api\Data\JobInterface;
use DotCommerce\CronScheduler\Api\Data\JobSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @api
 */
interface JobRepositoryInterface
{
    /**
     * @throws CouldNotSaveException
     */
    public function save(JobInterface $job): JobInterface;

    /**
     * @throws NoSuchEntityException
     */
    public function getById(int $entityId): JobInterface;

    /**
     * Load a job by its unique code.
     *
     * @throws NoSuchEntityException
     */
    public function getByCode(string $jobCode): JobInterface;

    /**
     * @throws CouldNotDeleteException
     */
    public function delete(JobInterface $job): bool;

    /**
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById(int $entityId): bool;

    public function getList(SearchCriteriaInterface $searchCriteria): JobSearchResultsInterface;
}
