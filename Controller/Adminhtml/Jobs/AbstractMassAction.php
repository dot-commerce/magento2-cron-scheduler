<?php
/**
 * @copyright Copyright (c) DotCommerce
 * @license   Open Software License 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Controller\Adminhtml\Jobs;

use DotCommerce\CronScheduler\Api\JobRepositoryInterface;
use DotCommerce\CronScheduler\Model\ResourceModel\Job\Collection;
use DotCommerce\CronScheduler\Model\ResourceModel\Job\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

abstract class AbstractMassAction extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'DotCommerce_CronScheduler::jobs';

    public function __construct(
        Action\Context $context,
        protected readonly Filter $filter,
        protected readonly CollectionFactory $collectionFactory,
        protected readonly JobRepositoryInterface $jobRepository,
        protected readonly LoggerInterface $logger
    ) {
        parent::__construct($context);
    }

    public function execute(): Redirect
    {
        /** @var Redirect $redirect */
        $redirect = $this->resultRedirectFactory->create();

        try {
            /** @var Collection $collection */
            $collection = $this->filter->getCollection($this->collectionFactory->create());

            if ($collection->getSize() > 0) {
                $this->process($collection);
            }
        } catch (LocalizedException $e) {
            // Expected, user-facing conditions (e.g. nothing selected) — show, don't log.
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            $this->messageManager->addErrorMessage(__('An error occurred while processing the selected jobs.'));
        }

        return $redirect->setPath('*/*/index');
    }

    abstract protected function process(Collection $collection): void;
}
