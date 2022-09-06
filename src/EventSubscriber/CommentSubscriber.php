<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Comment;
use DateTime;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CommentSubscriber implements EventSubscriberInterface
{
    public $tokenStorageInterface;

    public function __construct(TokenStorageInterface $tokenStorageInterface)
    {
        $this->tokenStorageInterface = $tokenStorageInterface;
    }

    public function setAuthor(ViewEvent $event)
    {
        $entity = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if ($entity instanceof Comment && $method == Request::METHOD_POST) {
            $author = $this->tokenStorageInterface->getToken()->getUser();
            $entity->setAuthor($author);
            $entity->setPublished(new DateTime(date('Y-m-d H:i:s')));
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['setAuthor', EventPriorities::PRE_WRITE],
        ];
    }
}
