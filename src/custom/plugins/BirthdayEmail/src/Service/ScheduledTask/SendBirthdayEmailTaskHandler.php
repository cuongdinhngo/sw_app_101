<?php declare(strict_types=1);

namespace BirthdayEmail\Service\ScheduledTask;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ContainerInterface;
use BirthdayEmail\Command\SendBirthdayEmailCommand;

#[AsMessageHandler(handles: SendBirthdayEmailTask::class)]
class SendBirthdayEmailTaskHandler extends ScheduledTaskHandler
{
    private LoggerInterface $logger;
    private SendBirthdayEmailCommand $command;

    public function __construct(
        EntityRepository $scheduledTaskRepository,
        LoggerInterface $logger,
        SendBirthdayEmailCommand $command
    ) {
        parent::__construct($scheduledTaskRepository);
        $this->logger = $logger;
        $this->command = $command;
    }

    public function run(): void
    {
        try {
            $this->logger->info('>>>>>>>> Start application ........');
            $input = new ArrayInput([]);
            $output = new BufferedOutput();

            $result = $this->command->run($input, $output);

            if ($result === Command::SUCCESS) {
                $this->logger->info('send-birthday-email command executed successfully.');
            } else {
                $this->logger->error('send-birthday-email command failed.');
            }

            $this->logger->info('Command output: ' . $output->fetch());
            $this->logger->info('>>>>>>>> DONE ........');
        } catch (\Exception $e) {
            $this->logger->error('Error executing send-birthday-email: ' . $e->getMessage());
        }
    }
}
