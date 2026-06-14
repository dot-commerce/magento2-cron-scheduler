<?php
/**
 * @copyright Copyright (c) DotCommerce
 * @license   Open Software License 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Controller\Adminhtml\Jobs;

use DotCommerce\CronScheduler\Api\Data\JobInterface;
use DotCommerce\CronScheduler\Model\ResourceModel\Job\Collection;

/**
 * Removes the registry rows for the selected jobs. Jobs still present in a
 * module's cron configuration are re-created with default settings on the
 * next synchronization run, so this effectively resets their overrides.
 */
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
