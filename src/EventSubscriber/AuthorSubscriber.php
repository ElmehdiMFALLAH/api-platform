<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use App\Entity\Post;

class AuthorSubscriber implements EventSubscriberInterface
{
    public $tokenStorageInterface;

    public function __construct(TokenStorageInterface $tokenStorageInterface)
    {
        $this->tokenStorageInterface = $tokenStorageInterface;
    }

    public function setPostAuthor(ViewEvent $event)
    {
        $entity = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if ($entity instanceof Post && $method == Request::METHOD_POST) {
            $author = $this->tokenStorageInterface->getToken()->getUser();
            $entity->setAuthor($author);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['setPostAuthor', EventPriorities::PRE_WRITE]
        ];
    }
}
