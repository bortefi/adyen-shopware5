<?php

namespace AdyenPayment\Components\NotificationProcessor;

use AdyenPayment\Components\PaymentStatusUpdate;
use AdyenPayment\Models\Event;
use AdyenPayment\Models\Notification;
use Psr\Log\LoggerInterface;
use Shopware\Components\ContainerAwareEventManager;
use Shopware\Models\Order\Status;

/**
 * Class RefundedReversed
 * @package AdyenPayment\Components\NotificationProcessor
 */
class RefundedReversed implements NotificationProcessorInterface
{
    const EVENT_CODE = 'REFUNDED_REVERSED';

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var ContainerAwareEventManager
     */
    private $eventManager;
    /**
     * @var PaymentStatusUpdate
     */
    private $paymentStatusUpdate;

    /**
     * Authorisation constructor.
     * @param LoggerInterface $logger
     * @param ContainerAwareEventManager $eventManager
     * @param PaymentStatusUpdate $paymentStatusUpdate
     */
    public function __construct(
        LoggerInterface $logger,
        ContainerAwareEventManager $eventManager,
        PaymentStatusUpdate $paymentStatusUpdate
    ) {
        $this->logger = $logger;
        $this->eventManager = $eventManager;
        $this->paymentStatusUpdate = $paymentStatusUpdate->setLogger($this->logger);
    }

    /**
     * Returns boolean on whether this processor can process the Notification object
     *
     * @param Notification $notification
     * @return boolean
     */
    public function supports(Notification $notification): bool
    {
        return strtoupper($notification->getEventCode()) === self::EVENT_CODE;
    }

    /**
     * Actual processing of the notification
     *
     * @param Notification $notification
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Enlight_Event_Exception
     */
    public function process(Notification $notification)
    {
        $order = $notification->getOrder();

        $this->eventManager->notify(
            Event::NOTIFICATION_PROCESS_REFUNDED_REVERSED,
            [
                'order' => $order,
                'notification' => $notification
            ]
        );

        $this->paymentStatusUpdate->updatePaymentStatus($order, Status::PAYMENT_STATE_COMPLETELY_PAID);
    }
}
