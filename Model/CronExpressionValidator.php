<?php
/**
 * @copyright Copyright (c) DotCommerce
 * @license   Open Software License 3.0 (OSL-3.0)
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Model;

use Magento\Cron\Model\ScheduleFactory;
use Magento\Framework\Exception\CronException;
use Magento\Framework\Exception\LocalizedException;

/**
 * Validates a cron expression using Magento's own parser, so that an admin's
 * Modified Schedule is rejected on save rather than silently breaking the job
 * at runtime. It mirrors exactly what {@see \Magento\Cron\Model\Schedule}
 * accepts (field count, ranges, modulus, month/day names).
 */
class CronExpressionValidator
{
    public function __construct(
        private readonly ScheduleFactory $scheduleFactory
    ) {
    }

    /**
     * @throws LocalizedException when the expression cannot be parsed.
     */
    public function validate(string $expression): void
    {
        $schedule = $this->scheduleFactory->create();

        try {
            // Validates the field count (5 or 6) and populates the parsed array.
            $schedule->setCronExpr($expression);

            // matchCronExpression() throws on a malformed token; calling it per
            // field avoids trySchedule()'s short-circuit skipping later fields.
            foreach ($schedule->getCronExprArr() as $field) {
                $schedule->matchCronExpression((string) $field, 0);
            }
        } catch (CronException $e) {
            throw new LocalizedException(
                __('"%1" is not a valid cron expression.', $expression),
                $e
            );
        }
    }
}
