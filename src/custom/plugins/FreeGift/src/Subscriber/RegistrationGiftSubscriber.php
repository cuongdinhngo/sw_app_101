<?php declare(strict_types=1);

namespace FreeGift\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Core\Checkout\Customer\Event\CustomerRegisterEvent;
use FreeGift\Service\FreeGiftService;
use Shopware\Core\Framework\Context;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Shopware\Core\Content\Mail\Service\MailService;
use Doctrine\DBAL\Connection;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Content\MailTemplate\Aggregate\MailTemplateTranslation\MailTemplateTranslationDefinition;
use Symfony\Component\Mime\Email;

class RegistrationGiftSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly FreeGiftService $freeGiftService,
        private readonly RequestStack $requestStack,
        private readonly MailService $mailService,
        private readonly Connection $connection
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        // Return the events to listen to as array like this:  <event to listen to> => <method to execute>
        return [
            CustomerRegisterEvent::class => 'onCustomerRegistrationLoaded'
        ];
    }

    public function onCustomerRegistrationLoaded(CustomerRegisterEvent $event)
    {
        $this->logger->debug('###[FREE_REGISTRATION_GIFT] Triggered CustomerRegisterEvent ...');
        $this->showFreeGiftNotification();

        $customer = $event->getCustomer();
        $this->freeGiftService->assignFreeGift($customer->getId(), Context::createDefaultContext());

        if ($this->sendEmail($customer)) {
            $this->logger->debug(
                '###[FREE_REGISTRATION_GIFT] Free registration gift email was sent',
                [
                    'customerId' => $customer->getId()
                ]
            );
        }
    }

    private function sendEmail(CustomerEntity $customer): ?Email
    {
        $mailTemplate = $this->getMailTemplate()[0];
        $data = $this->composeMailData($mailTemplate, $customer);
        
        return $this->mailService->send($data, Context::createDefaultContext(), $data['mailTemplateData'] ?? []);
    }

    private function showFreeGiftNotification(): void
    {
        $session = $this->requestStack->getMainRequest()->getSession();
        $session->getFlashBag()->add('success', 'Congratulations! Let take your first shopping and get your free gift when you checkout');
    }

    private function getMailTemplate(): array
    {
        $query = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(MailTemplateTranslationDefinition::ENTITY_NAME)
            ->where('description LIKE :searchTerm')
            ->setParameter('searchTerm', '%Free Registration Gift%');
        
        $this->logger->debug('[SQL_QUERY]', [$query->getSQL(), $query->getParameters()]);

        return $query->executeQuery()->fetchAllAssociative();
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
}
