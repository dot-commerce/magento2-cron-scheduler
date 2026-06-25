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
use DotCommerce\CronScheduler\Model\JobScheduler;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class Schedule extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'DotCommerce_CronScheduler::jobs';

    public function __construct(
        Action\Context $context,
        private readonly JobRepositoryInterface $jobRepository,
        private readonly JobScheduler $jobScheduler,
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
            $this->messageManager->addErrorMessage(__('No job was specified to schedule.'));

            return $redirect->setPath('*/*/index');
        }

        try {
            $job = $this->jobRepository->getById($jobId);

            if (!$job->isEnabled()) {
                $this->messageManager->addErrorMessage(__('A disabled job cannot be scheduled.'));

                return $redirect->setPath('*/*/index');
            }

            $this->jobScheduler->scheduleNow($job);

            $this->messageManager->addSuccessMessage(
                __('Job "%1" has been scheduled to run on the next cron tick.', $job->getJobCode())
            );
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('The requested job no longer exists.'));
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            $this->messageManager->addErrorMessage(__('Could not schedule the job: %1', $e->getMessage()));
        }

        return $redirect->setPath('*/*/index');
    }
}
