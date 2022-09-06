<?php

namespace App\Controller;

use ApiPlatform\Core\Validator\Exception\ValidationException as ExceptionValidationException;
use App\Entity\Image;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use ApiPlatform\Core\Validator\ValidatorInterface;

class UploadImageAction
{
    private $formFactory;
    private $entityManager;
    private $validator;

    public function __construct(FormFactoryInterface $formFactory, EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    public function __invoke(Request $request)
    {
        $image = new Image;

        $form = $this->formFactory->create(null, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($image);
            $this->entityManager->flush();

            return $image;
        }

        throw new ExceptionValidationException(
            $this->validator->validate($image)
        );
    }
}
u