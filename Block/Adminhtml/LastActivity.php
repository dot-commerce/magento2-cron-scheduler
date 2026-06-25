<?php
/**
 * @author    Mudassar Iqbal <miqbal@dotcommerce.co>
 * @copyright Copyright (c) Dot Commerce
 * @license   MIT
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Block\Adminhtml;

use DotCommerce\CronScheduler\Model\CronActivity;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * "Last Cron Activity" banner
 */
class LastActivity extends Template
{
    protected $_template = 'DotCommerce_CronScheduler::last_activity.phtml';

    private bool $loaded = false;

    private ?string $lastActivity = null;

    public function __construct(
        Context $context,
        private readonly CronActivity $cronActivity,
        private readonly DateTime $dateTime,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function hasActivity(): bool
    {
        return $this->getRawLastActivity() !== null;
    }

    public function getHumanAge(): ?string
    {
        $age = $this->getAgeInSeconds();

        if ($age === null) {
            return null;
        }

        if ($age < 60) {
            return (string) __('%1 second(s) ago', $age);
        }

        if ($age < 3600) {
            return (string) __('%1 minute(s) ago', intdiv($age, 60));
        }

        if ($age < 86400) {
            return (string) __('%1 hour(s) ago', intdiv($age, 3600));
        }

        return (string) __('%1 day(s) ago', intdiv($age, 86400));
    }

    private function getAgeInSeconds(): ?int
    {
        $raw = $this->getRawLastActivity();

        if ($raw === null) {
            return null;
        }

        $finishedTs = (new \DateTime($raw, new \DateTimeZone('UTC')))->getTimestamp();

        return max(0, $this->dateTime->gmtTimestamp() - $finishedTs);
    }

    private function getRawLastActivity(): ?string
    {
        if (!$this->loaded) {
            $this->lastActivity = $this->cronActivity->getLastHeartbeatAt();
            $this->loaded = true;
        }

        return $this->lastActivity;
    }
}
