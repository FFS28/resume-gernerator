<?php

declare(strict_types=1);

namespace App\Listener;

use App\Service\FlashbagService;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityDeletedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class EasyAdminListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly FlashbagService     $flashbagService,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[ArrayShape([AfterEntityPersistedEvent::class => "string[]", AfterEntityUpdatedEvent::class => "string[]", AfterEntityDeletedEvent::class => "string[]"])]
    public static function getSubscribedEvents(): array
    {
        return [
            AfterEntityPersistedEvent::class => ['flashMessageAfterPersist'],
            AfterEntityUpdatedEvent::class   => ['flashMessageAfterUpdate'],
            AfterEntityDeletedEvent::class   => ['flashMessageAfterDelete'],
        ];
    }

    public function flashMessageAfterPersist(AfterEntityPersistedEvent $event): void
    {
        $entityInstance = $event->getEntityInstance();
        if (method_exists($entityInstance, 'setTranslator')) {
            $entityInstance->setTranslator($this->translator);
        }
        $this->flashbagService->send('create', $entityInstance);
    }

    public function flashMessageAfterUpdate(AfterEntityUpdatedEvent $event): void
    {
        $entityInstance = $event->getEntityInstance();
        if (method_exists($entityInstance, 'setTranslator')) {
            $entityInstance->setTranslator($this->translator);
        }
        $this->flashbagService->send('update', $entityInstance);
    }

    public function flashMessageAfterDelete(AfterEntityDeletedEvent $event): void
    {
        $entityInstance = $event->getEntityInstance();
        if (method_exists($entityInstance, 'setTranslator')) {
            $entityInstance->setTranslator($this->translator);
        }
        $this->flashbagService->send('delete', $entityInstance);
    }
}