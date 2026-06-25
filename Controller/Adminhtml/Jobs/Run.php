<?php
/**
 * @author    Mudassar Iqbal <miqbal@dotcommerce.co>
 * @copyright Copyright (c) Dot Commerce
 * @license   MIT
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Controller\Adminhtml\Jobs;

use DotCommerce\CronScheduler\Api\Data\JobInterface;
use DotCommerce\CronScheduler\Api\JobRepositoryInterface;
use DotCommerce\CronScheduler\Model\JobRunner;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

/**
 * Force-runs a single managed job now (per-row "Run Job" action on the jobs grid).
 */
class Run extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'DotCommerce_CronScheduler::jobs';

    public function __construct(
        Action\Context $context,
        private readonly JobRepositoryInterface $jobRepository,
        private readonly JobRunner $jobRunner,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct($context);
    }

    public function execute(): Redirect
    {
        /** @var Redirect $redirect */
        $redirect = $this->resultRedirectFactory->create();
        $jobId = (int) $this->getRequest()->getParam(JobInterface::ENTITY_ID);

        if ($jobId <= 0) {
            $this->messageManager->addErrorMessage(__('No job was specified to run.'));

            return $redirect->setPath('*/*/index');
        }

        try {
            $job = $this->jobRepository->getById($jobId);
            $result = $this->jobRunner->run($job);

            $this->messageManager->addSuccessMessage(
                __('Job "%1" ran successfully. %2.', $job->getJobCode(), $result)
            );
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('The requested job no longer exists.'));
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            $this->messageManager->addErrorMessage(__('Job run failed: %1', $e->getMessage()));
        }

        return $redirect->setPath('*/*/index');
    }
}
