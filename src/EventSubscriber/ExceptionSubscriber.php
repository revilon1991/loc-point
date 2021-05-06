<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    public function onException(ExceptionEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $throwable = $event->getThrowable();

        $this->logger->critical('Exception on route "{route}": {throwable}', [
            'route' => $request->attributes->get('_route'),
            'throwable' => $throwable,
            'request' => $request,
        ]);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ExceptionEvent::class => ['onException'],
        ];
    }
}
