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
use DotCommerce\CronScheduler\Model\CronExpressionValidator;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;

class InlineEdit extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'DotCommerce_CronScheduler::jobs';

    public function __construct(
        Action\Context $context,
        private readonly JobRepositoryInterface $jobRepository,
        private readonly JsonFactory $jsonFactory,
        private readonly CronExpressionValidator $cronExpressionValidator
    ) {
        parent::__construct($context);
    }

    public function execute(): Json
    {
        /** @var Json $result */
        $result = $this->jsonFactory->create();
        $messages = [];
        $error = false;

        if (!$this->getRequest()->getParam('isAjax')) {
            return $result->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        $items = $this->getRequest()->getParam('items', []);

        foreach ($items as $item) {
            $jobId = (int) ($item[JobInterface::ENTITY_ID] ?? 0);

            try {
                $job = $this->jobRepository->getById($jobId);

                $modified = trim((string) ($item[JobInterface::MODIFIED_SCHEDULE] ?? ''));
                if ($modified !== '') {
                    $this->cronExpressionValidator->validate($modified);
                }
                // An empty value clears the override and falls back to the original schedule.
                $job->setModifiedSchedule($modified === '' ? null : $modified);

                if (array_key_exists(JobInterface::STATUS, $item)) {
                    $status = (int) $item[JobInterface::STATUS];
                    if (!in_array($status, [JobInterface::STATUS_ENABLED, JobInterface::STATUS_DISABLED], true)) {
                        throw new LocalizedException(__('Invalid status value supplied.'));
                    }
                    $job->setStatus($status);
                }

                $this->jobRepository->save($job);
            } catch (\Exception $e) {
                $messages[] = __('Job "%1": %2', $jobId, $e->getMessage());
                $error = true;
            }
        }

        if (!$error) {
            $messages[] = __('Changes have been saved.');
        }

        return $result->setData([
            'messages' => $messages,
            'error' => $error,
        ]);
    }
}
