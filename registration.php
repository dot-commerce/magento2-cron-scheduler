<?php
/**
 * DotCommerce Cron Scheduler for Magento 2.
 *
 * @copyright Copyright (c) DotCommerce
 * @license   Open Software License 3.0 (OSL-3.0)
 */

declare(strict_types=1);

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'DotCommerce_CronScheduler',
    __DIR__
);
