<?php declare(strict_types=1);

namespace BirthdayEmail\Service\ScheduledTask;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class SendBirthdayEmailTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'customPlugin.send_birthday_email';
    }

    public static function getDefaultInterval(): int
    {
        return 300;
    }
}
