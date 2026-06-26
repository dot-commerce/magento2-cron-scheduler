<?php
/**
 * @author    Mudassar Iqbal <miqbal@dotcommerce.co>
 * @copyright Copyright (c) Dot Commerce
 * @license   MIT
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class EmailNotificationConfig
{
    private const XML_PATH_ENABLED = 'dc_cronscheduler/email_notification/enabled';
    private const XML_PATH_SENDER = 'dc_cronscheduler/email_notification/sender_email';
    private const XML_PATH_SEND_TO = 'dc_cronscheduler/email_notification/send_to';
    private const XML_PATH_SUBJECT = 'dc_cronscheduler/email_notification/email_subject';
    private const XML_PATH_CONTENT = 'dc_cronscheduler/email_notification/email_content';
    private const XML_PATH_INTERVAL = 'dc_cronscheduler/email_notification/notification_interval';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED);
    }

    public function getSenderIdentity(): string
    {
        return (string) ($this->scopeConfig->getValue(self::XML_PATH_SENDER) ?: 'general');
    }

    /**
     * @return string[] Trimmed, non-empty recipient addresses.
     */
    public function getRecipients(): array
    {
        $raw = (string) $this->scopeConfig->getValue(self::XML_PATH_SEND_TO);
        $lines = preg_split('/[\r\n]+/', $raw, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_filter(array_map('trim', $lines)));
    }

    public function getEmailSubject(): string
    {
        return (string) $this->scopeConfig->getValue(self::XML_PATH_SUBJECT);
    }

    public function getEmailContent(): string
    {
        return (string) $this->scopeConfig->getValue(self::XML_PATH_CONTENT);
    }

    /**
     * Anti-spam throttle window in seconds (0 = notify on every new failure).
     */
    public function getIntervalSeconds(): int
    {
        return max(0, (int) $this->scopeConfig->getValue(self::XML_PATH_INTERVAL)) * 60;
    }
}
