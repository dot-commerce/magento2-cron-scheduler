<?php
/**
 * @author    Mudassar Iqbal <miqbal@dotcommerce.co>
 * @copyright Copyright (c) Dot Commerce
 * @license   MIT
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Controller\Adminhtml\Jobs;

use DotCommerce\CronScheduler\Api\Data\JobInterface;
use DotCommerce\CronScheduler\Model\ResourceModel\Job\Collection;

class MassDelete extends AbstractMassAction
{
    protected function process(Collection $collection): void
    {
        $deleted = 0;

        /** @var JobInterface $job */
        foreach ($collection as $job) {
            $this->jobRepository->delete($job);
            $deleted++;
        }

        if ($deleted > 0) {
            $this->messageManager->addSuccessMessage(__('%1 job(s) have been deleted.', $deleted));
        }
    }
}
