<?php
/**
 * @author    Mudassar Iqbal <miqbal@dotcommerce.co>
 * @copyright Copyright (c) Dot Commerce
 * @license   MIT
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Model\FailedJobs;

use DotCommerce\CronScheduler\Model\Config\EmailNotificationConfig;
use Magento\Framework\App\Area;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\Store;

class EmailSender
{
    private const TEMPLATE_ID = 'dc_cronscheduler_error_template';

    public function __construct(
        private readonly EmailNotificationConfig $config,
        private readonly TransportBuilder $transportBuilder,
        private readonly Escaper $escaper
    ) {
    }

    /**
     * @param array{job_code?: ?string, executed_at?: ?string, messages?: ?string} $failedSchedule
     */
    public function send(array $failedSchedule): void
    {
        $recipients = $this->config->getRecipients();

        if (!$recipients) {
            return;
        }

        $subject = $this->substitute($this->config->getEmailSubject(), $failedSchedule, false);
        $content = $this->substitute($this->config->getEmailContent(), $failedSchedule, true);

        $this->transportBuilder
            ->setTemplateIdentifier(self::TEMPLATE_ID)
            ->setTemplateOptions(['area' => Area::AREA_ADMINHTML, 'store' => Store::DEFAULT_STORE_ID])
            ->setTemplateVars(['subject' => $subject, 'emailContent' => $content])
            ->setFromByScope($this->config->getSenderIdentity());

        foreach ($recipients as $recipient) {
            $this->transportBuilder->addTo($recipient);
        }

        $this->transportBuilder->getTransport()->sendMessage();
    }

    /**
     * @param array{job_code?: ?string, executed_at?: ?string, messages?: ?string} $schedule
     */
    private function substitute(string $template, array $schedule, bool $escapeMessage): string
    {
        $message = (string) ($schedule['messages'] ?? '');

        return strtr($template, [
            '{{job_code}}' => (string) ($schedule['job_code'] ?? ''),
            '{{executed_at}}' => (string) ($schedule['executed_at'] ?? ''),
            '{{message}}' => $escapeMessage ? $this->escaper->escapeHtml($message) : $message,
        ]);
    }
}
