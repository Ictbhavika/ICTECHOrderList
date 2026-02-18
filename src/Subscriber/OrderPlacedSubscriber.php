<?php

declare(strict_types=1);

namespace ICTECHOrderList\Subscriber;

use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class OrderPlacedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityRepository $orderListRepository,
        private readonly RequestStack $requestStack
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckoutOrderPlacedEvent::class => 'onOrderPlaced'
        ];
    }

    public function onOrderPlaced(CheckoutOrderPlacedEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }

        $session = $request->getSession();
        $orderListId = $session->get('order_list_id_to_delete');

        if ($orderListId) {
            try {
                $this->orderListRepository->delete([
                    ['id' => $orderListId]
                ], $event->getContext());
                
                $session->remove('order_list_id_to_delete');
            } catch (\Exception $e) {
                // Silent fail
            }
        }
    }
}
