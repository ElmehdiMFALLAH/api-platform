<?php

namespace App\Controller;

use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ResetPasswordAction
{
    private $validator;
    private $encoder;
    private $entityManager;
    private $tokenManager;
    public function __construct(ValidatorInterface $validator, UserPasswordEncoderInterface $encoder, EntityManagerInterface $entityManager, JWTTokenManagerInterface $tokenManager)
    {
        $this->validator = $validator;
        $this->encoder = $encoder;
        $this->entityManager = $entityManager;
        $this->tokenManager = $tokenManager;
    }

    public function __invoke(User $user)
    {
        $this->validator->validate($user);
        $user->setPassword($this->encoder->encodePassword($user, $user->getNewPassword()));
        $this->entityManager->flush();
        $token = $this->tokenManager->create($user);

        return new JsonResponse(['token' => $token]);
    }
}
