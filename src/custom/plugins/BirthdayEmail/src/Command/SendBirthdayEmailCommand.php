<?php declare(strict_types=1);

namespace BirthdayEmail\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use DateTime;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\AndFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Symfony\Component\Console\Input\InputOption;
use Shopware\Core\Content\Mail\Service\MailService;
use Shopware\Core\Content\MailTemplate\Aggregate\MailTemplateTranslation\MailTemplateTranslationEntity;
use Shopware\Core\Content\MailTemplate\Aggregate\MailTemplateTranslation\MailTemplateTranslationDefinition;
use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Psr\Log\LoggerInterface;

#[AsCommand(
    name: 'send-birthday-email',
    description: 'Send Birthday email',
)]
class SendBirthdayEmailCommand extends Command
{
    private const DEFAULT_MAIL_TEMPLATE_ID = '019482b3bfaa78b7b6dc8f17252d0098';

    public function __construct(
        private readonly EntityRepository $customerRepository,
        private readonly EntityRepository $mailTemplateTranslationRepository,
        private readonly MailService $mailService,
        private readonly ContainerInterface $container,
        private LoggerInterface $logger
    ) {
        parent::__construct();
    }

    // Provides a description, printed out in bin/console
    protected function configure(): void
    {
        $this->setDescription('Send birthday email');
        $this->addOption('templateId', null, InputOption::VALUE_OPTIONAL, 'Mail template id');
    }

    // Actual code executed in the command
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->info('####### Execute command ####### ');
        $output->writeln('Start sending birthday email .....');
        $context = Context::createCLIContext();

        $templateId = $input->getOption('templateId') ?? self::DEFAULT_MAIL_TEMPLATE_ID;
        $mailTemplate = $this->getMailTemplate($templateId, $context)[0];

        $today = (new DateTime())->format('Y-m-d');
        $today = '1990-01-01';
        $customers = $this->getUsersByBirthday($today, $context);


        foreach ($customers as $id => $customer) {
            var_dump($id . ' => ' . $customer->getFirstName() . ' ' . $customer->getLastName() . ' -> ' . $customer->getEmail());
            $data = $this->composeMailData($mailTemplate, $customer);
            $message = $this->mailService->send($data, $context, $data['mailTemplateData'] ?? []);
            var_dump($message ? $message->toString() : '####');
        }

        $output->writeln('DONE');
        $this->logger->info('####### COMMAND DONE ####### ');

        return 0;
    }

    private function composeMailData(array $mailTemplate, CustomerEntity $customer): array
    {
        return [
            'contentHtml' => $mailTemplate['content_html'],
            'contentPlain' => $mailTemplate['content_plain'],
            'recipients' => [
                $customer->getEmail() => $customer->getEmail()
            ],
            'mailTemplateData' => [
                'contactFormData' => [
                    'firstName' => $customer->getFirstName(),
                    'lastName' =>  $customer->getLastName(),
                ]
            ],
            'salesChannelId' => $customer->getSalesChannelId(),
            'subject' => $mailTemplate['subject'],
            'senderName' => $mailTemplate['sender_name'],
        ];
    }

    private function getUsersByBirthday(string $today, Context $context): EntityCollection
    {
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('birthday', $today))
            ->addFilter(new EqualsFilter('customFields.enable_birthday_email', 1));

        return $this->customerRepository->search($criteria, $context)->getEntities();
    }

    private function getMailTemplate(string $templateId, Context $context)
    {
        $connection = $this->container->get(Connection::class);
        $result = $connection->createQueryBuilder()
            ->select('*')
            ->from(MailTemplateTranslationDefinition::ENTITY_NAME)
            ->where('`mail_template_id` = :id')
            ->setParameter('id', Uuid::fromHexToBytes($templateId))
            ->executeQuery()
            ->fetchAllAssociative();

        return $result;
    }
}
