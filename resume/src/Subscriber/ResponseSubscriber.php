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

        $response->headers->set("Content-Security-Policy",
            "script-src 'self' " . $request->getSchemeAndHttpHost() . " https://raw.githack.com/eKoopmans/html2pdf/ https://www.google.com/ https://www.gstatic.com/ 'unsafe-inline';"
            . "style-src 'self' " . $request->getSchemeAndHttpHost() . " https://fonts.gstatic.com https://fonts.googleapis.com 'unsafe-inline';"
        );

        $response->headers->set("X-Frame-Options", 'deny');
        $response->headers->set("X-XSS-Protection", '1; mode=block');
        $response->headers->set("X-Content-Type-Options", 'nosniff');
        $response->headers->set("Access-Control-Allow-Origin", 'http://localhost:32847/');
        $response->setVary('Accept-Encoding');
    }
}
