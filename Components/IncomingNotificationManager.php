<?php

namespace AdyenPayment\Components;

use AdyenPayment\Components\Builder\NotificationBuilder;
use AdyenPayment\Exceptions\InvalidParameterException;
use AdyenPayment\Exceptions\OrderNotFoundException;
use AdyenPayment\Models\Feedback\NotificationItemFeedback;
use AdyenPayment\Models\Feedback\TextNotificationItemFeedback;
use AdyenPayment\Models\TextNotification;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;
use Shopware\Components\Model\ModelManager;

/**
 * Class IncomingNotificationManager
 * @package AdyenPayment\Components
 */
class IncomingNotificationManager
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var NotificationBuilder
     */
    private $notificationBuilder;

    /**
     * @var ModelManager
     */
    private $entityManager;

    /**
     * IncomingNotificationManager constructor.
     * @param LoggerInterface $logger
     * @param NotificationBuilder $notificationBuilder
     * @param ModelManager $entityManager
     */
    public function __construct(
        LoggerInterface $logger,
        NotificationBuilder $notificationBuilder,
        ModelManager $entityManager
    ) {
        $this->logger = $logger;
        $this->notificationBuilder = $notificationBuilder;
        $this->entityManager = $entityManager;
    }

    /**
     * @param TextNotification[] $textNotifications
     * @throws ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function convertNotifications(array $textNotifications)
    {
        foreach ($textNotifications as $textNotificationItem) {
            try {
                if (!empty($textNotificationItem->getTextNotification())) {
                    $notification = $this->notificationBuilder->fromParams(
                        json_decode($textNotificationItem->getTextNotification(), true)
                    );
                    $this->entityManager->persist($notification);
                }
            } catch (InvalidParameterException $exception) {
                $this->logger->warning(
                    $exception->getMessage() . " " . $textNotificationItem->getTextNotification()
                );
            } catch (OrderNotFoundException $exception) {
                $this->logger->warning(
                    $exception->getMessage() . " " . $textNotificationItem->getTextNotification()
                );
            }
            $this->entityManager->remove($textNotificationItem);
        }
        $this->entityManager->flush();
    }

    /**
     * @param array $textNotificationItems
     * @return \Generator
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function saveTextNotification(array $textNotificationItems): \Generator
    {
        foreach ($textNotificationItems as $textNotificationItem) {
            try {
                if (!empty($textNotificationItem['NotificationRequestItem'])) {
                    if ($this->skipNotification($textNotificationItem['NotificationRequestItem'])) {
                        $this->logger->info(
                            'Skipped notification',
                            ['eventCode' => $notificationRequest['eventCode'] ?? '']
                        );
                        continue;
                    }

                    $textNotification = new TextNotification();
                    $textNotification->setTextNotification(
                        json_encode($textNotificationItem['NotificationRequestItem'])
                    );
                    $this->entityManager->persist($textNotification);
                }
            } catch (ORMException $exception) {
                $this->logger->warning($exception->getMessage());
                yield new TextNotificationItemFeedback($exception->getMessage(), $textNotificationItem);
            }
        }
        $this->entityManager->flush();
    }

    private function skipNotification(array $notificationRequest): bool
    {
        if (!empty($notificationRequest['eventCode']) &&
            strpos($notificationRequest['eventCode'], 'REPORT_') !== false) {
            return true;
        }

        return false;
    }
}
