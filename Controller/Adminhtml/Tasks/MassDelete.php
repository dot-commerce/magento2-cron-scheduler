<?php
/**
 * @author    Mudassar Iqbal <miqbal@dotcommerce.co>
 * @copyright Copyright (c) Dot Commerce
 * @license   MIT
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Controller\Adminhtml\Tasks;

use DotCommerce\CronScheduler\Model\ResourceModel\Schedule\Grid\Collection;
use DotCommerce\CronScheduler\Model\ResourceModel\Schedule\Grid\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Cron\Model\ResourceModel\Schedule as ScheduleResource;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

/**
 * Deletes the selected cron_schedule history rows.
 */
class MassDelete extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'DotCommerce_CronScheduler::tasks';

    public function __construct(
        Action\Context $context,
        private readonly Filter $filter,
        private readonly CollectionFactory $collectionFactory,
        private readonly ScheduleResource $scheduleResource,
        private readonly LoggerInterface $logger
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
            $ids = $collection->getAllIds();

            if ($ids) {
                $connection = $this->scheduleResource->getConnection();
                $connection->delete(
                    $this->scheduleResource->getMainTable(),
                    [$this->scheduleResource->getIdFieldName() . ' IN (?)' => $ids]
                );
            }

            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been deleted.', count($ids))
            );
        } catch (LocalizedException $e) {
            // Expected, user-facing conditions (e.g. nothing selected) - show, don't log.
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            $this->messageManager->addErrorMessage(
                __('An error occurred while deleting the selected records.')
            );
        }

        return $redirect->setPath('*/*/index');
    }
}
