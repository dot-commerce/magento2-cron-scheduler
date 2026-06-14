<?php
/**
 * @copyright Copyright (c) DotCommerce
 * @license   Open Software License 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Controller\Adminhtml\Jobs;

use DotCommerce\CronScheduler\Api\Data\JobInterface;
use DotCommerce\CronScheduler\Model\ResourceModel\Job\Collection;
use DotCommerce\CronScheduler\Model\Source\Status;

class MassEnable extends AbstractMassAction
{
    protected function process(Collection $collection): void
    {
        $updated = 0;

        /** @var JobInterface $job */
        foreach ($collection as $job) {
            if (!$job->isEnabled()) {
                $job->setStatus(Status::ENABLED->value);
                $this->jobRepository->save($job);
                $updated++;
            }
        }

        if ($updated > 0) {
            $this->messageManager->addSuccessMessage(__('%1 job(s) have been enabled.', $updated));
        }
    }
}
