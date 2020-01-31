<?php

namespace App\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class ResponseSubscriber
 * @package AppBundle\Subscriber
 */
class ResponseSubscriber implements EventSubscriberInterface
{
    /** @inheritdoc */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => 'onResponse'
        ];
    }

    /**
     * Callback function for event subscriber
     * @param ResponseEvent $event
     */
    public function onResponse(ResponseEvent $event)
    {
        $response = $event->getResponse();
        $request = $event->getRequest();

        if (substr($request->getRequestUri(), 0, 6) !== '/admin') {
            $response->headers->set("Content-Security-Policy", "script-src 'self' " . $request->getSchemeAndHttpHost());
        }
        $response->headers->set("X-Frame-Options", 'deny');
        $response->headers->set("X-XSS-Protection", '1; mode=block');
        $response->headers->set("X-Content-Type-Options", 'nosniff');
        $response->setVary('Accept-Encoding');
    }
}