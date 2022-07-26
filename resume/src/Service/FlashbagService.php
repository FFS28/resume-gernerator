<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatableMessage;

class FlashbagService
{
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function send(string $action, $entity = null, $parameters = [], $type = 'success'): void
    {
        if ($entity) {
            $className = str_replace('App\\Entity\\', '', (string)$entity::class);
            $parameters['%entityType%'] = new TranslatableMessage($className);
            $parameters['%entityName%'] = (string)$entity;
        }

        $this->requestStack->getSession()->getFlashBag()->add(
            $type, new TranslatableMessage('flash_message.' . $action, $parameters, 'messages')
        );
    }
}