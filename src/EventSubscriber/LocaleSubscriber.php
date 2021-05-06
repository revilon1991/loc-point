<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LocaleSubscriber implements EventSubscriberInterface
{
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $session = $request->getSession();

        if (!$request->hasPreviousSession()) {
            return;
        }

        $locale = $session->get('_locale', $request->getDefaultLocale());
        $request->setLocale($locale);
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $request = $event->getRequest();
        $session = $request->getSession();
        $user = $event->getUser();

        if (!$user instanceof User || !$user->getLocale()) {
            return;
        }

        $session->set('_locale', $user->getLocale());
        $request->setLocale($user->getLocale());
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => [['onKernelRequest', 20]],
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }
}
