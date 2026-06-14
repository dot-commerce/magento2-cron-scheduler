<?php
/**
 * @copyright Copyright (c) DotCommerce
 * @license   Open Software License 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Model;

use DotCommerce\CronScheduler\Api\Data\JobInterface;
use DotCommerce\CronScheduler\Api\Data\JobSearchResultsInterface;
use DotCommerce\CronScheduler\Api\Data\JobSearchResultsInterfaceFactory;
use DotCommerce\CronScheduler\Api\JobRepositoryInterface;
use DotCommerce\CronScheduler\Model\ResourceModel\Job as JobResource;
use DotCommerce\CronScheduler\Model\ResourceModel\Job\Collection;
use DotCommerce\CronScheduler\Model\ResourceModel\Job\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class JobRepository implements JobRepositoryInterface
{
    public function __construct(
        private readonly JobResource $resource,
        private readonly JobFactory $jobFactory,
        private readonly CollectionFactory $collectionFactory,
        private readonly JobSearchResultsInterfaceFactory $searchResultsFactory,
        private readonly CollectionProcessorInterface $collectionProcessor
    ) {
    }

    public function save(JobInterface $job): JobInterface
    {
        try {
            $this->resource->save($job);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __('Could not save the cron job: %1', $e->getMessage()),
                $e
            );
        }

        return $job;
    }

    public function getById(int $entityId): JobInterface
    {
        $job = $this->jobFactory->create();
        $this->resource->load($job, $entityId);

        if (!$job->getEntityId()) {
            throw new NoSuchEntityException(
                __('No cron job exists with ID "%1".', $entityId)
            );
        }

        return $job;
    }

    public function getByCode(string $jobCode): JobInterface
    {
        $job = $this->jobFactory->create();
        $this->resource->load($job, $jobCode, JobInterface::JOB_CODE);

        if (!$job->getEntityId()) {
            throw new NoSuchEntityException(
                __('No cron job exists with code "%1".', $jobCode)
            );
        }

        return $job;
    }

    public function delete(JobInterface $job): bool
    {
        try {
            $this->resource->delete($job);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(
                __('Could not delete the cron job: %1', $e->getMessage()),
                $e
            );
        }

        return true;
    }

    public function deleteById(int $entityId): bool
    {
        return $this->delete($this->getById($entityId));
    }

    public function getList(SearchCriteriaInterface $searchCriteria): JobSearchResultsInterface
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var JobSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }
}
