<?php
/**
 * @author    Mudassar Iqbal <miqbal@dotcommerce.co>
 * @copyright Copyright (c) Dot Commerce
 * @license   MIT
 */

declare(strict_types=1);

namespace DotCommerce\CronScheduler\Console\Command;

use DotCommerce\CronScheduler\Api\JobRepositoryInterface;
use DotCommerce\CronScheduler\Model\JobRunner;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends AbstractJobCommand
{
    public function __construct(
        JobRepositoryInterface $jobRepository,
        private readonly JobRunner $jobRunner,
        private readonly State $appState
    ) {
        parent::__construct($jobRepository);
    }

    protected function configure(): void
    {
        $this->setName('dc:cron:run')
            ->setDescription('Force-run a managed cron job immediately, regardless of its schedule.');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $jobCode = (string) $input->getArgument(self::ARG_JOB_CODE);
        $job = $this->getJob($jobCode, $output);

        if ($job === null) {
            return self::FAILURE;
        }

        $output->writeln(sprintf('Running job "%s"...', $jobCode));

        try {
            // Run in the crontab area, matching how the scheduler executes jobs.
            $result = $this->appState->emulateAreaCode(
                Area::AREA_CRONTAB,
                fn (): string => $this->jobRunner->run($job)
            );
        } catch (\Throwable $e) {
            $output->writeln(sprintf('<error>Job "%s" failed: %s</error>', $jobCode, $e->getMessage()));

            return self::FAILURE;
        }

        $output->writeln(sprintf('<info>Job "%s" ran successfully. %s.</info>', $jobCode, $result));

        return self::SUCCESS;
    }
}
