<?php
/**
 * @author    Mudassar Iqbal <miqbal@dotcommerce.co>
 * @copyright Copyright (c) Dot Commerce
 * @license   MIT
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Model;

use Magento\Cron\Model\ScheduleFactory;
use Magento\Framework\Exception\CronException;
use Magento\Framework\Exception\LocalizedException;

/**
 * Validates a cron expression before it is stored as a Modified Schedule
 */
class CronExpressionValidator
{
    /**
     * Inclusive [min, max] bounds for the five standard cron fields, in order:
     * minute, hour, day-of-month, month, day-of-week (0 and 7 both mean Sunday).
     * The optional 6th (year) field is left to Magento's structural check.
     */
    private const FIELD_BOUNDS = [
        [0, 59],
        [0, 23],
        [1, 31],
        [1, 12],
        [0, 7],
    ];

    public function __construct(
        private readonly ScheduleFactory $scheduleFactory
    ) {
    }

    /**
     * @throws LocalizedException when the expression cannot be parsed or is out of range.
     */
    public function validate(string $expression): void
    {
        $schedule = $this->scheduleFactory->create();
        $fields = [];

        try {
            // Validates the field count (5 or 6) and populates the parsed array.
            $schedule->setCronExpr($expression);
            $fields = $schedule->getCronExprArr();

            // matchCronExpression() throws on a malformed token; calling it per
            // field avoids trySchedule()'s short-circuit skipping later fields.
            foreach ($fields as $field) {
                $schedule->matchCronExpression((string) $field, 0);
            }
        } catch (CronException $e) {
            throw new LocalizedException(
                __('"%1" is not a valid cron expression.', $expression),
                $e
            );
        }

        foreach (self::FIELD_BOUNDS as $index => [$min, $max]) {
            if (!$this->isWithinRange((string) $fields[$index], $min, $max)) {
                throw new LocalizedException(
                    __('"%1" is not a valid cron expression.', $expression)
                );
            }
        }
    }

    /**
     * Checks every numeric token in a single field against its bounds. Non-numeric
     * tokens (`*`, month/day names) are already structurally valid and in range by
     * definition, and the step value after `/` is a modulus, not a field value.
     */
    private function isWithinRange(string $field, int $min, int $max): bool
    {
        foreach (explode(',', $field) as $part) {
            $value = explode('/', $part)[0];

            if ($value === '*') {
                continue;
            }

            foreach (explode('-', $value) as $bound) {
                if ($bound !== '' && ctype_digit($bound)
                    && ((int) $bound < $min || (int) $bound > $max)
                ) {
                    return false;
                }
            }
        }

        return true;
    }
}
