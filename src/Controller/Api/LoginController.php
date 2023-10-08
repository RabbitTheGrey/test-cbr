<?php

namespace App\Controller\Api;

use App\Controller\BaseController;
use App\Entity\User;
use App\Form\LoginRequestType;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * по идее этого класса не должно быть, тк фреймворк содержит бандл для авторизации,
 * однако user_id в таких бандлах не используется, тк достаточно одного лишь токена для однозначного определения юзера.
 * Все это существует только для соблюдения условий задачи.
 * Токен хранится в бд по этой же причине.
 */
class LoginController extends BaseController
{
    /**
     * @var UserPasswordHasherInterface
     */
    private UserPasswordHasherInterface $hasher;

    /**
     * @var FormFactoryInterface
     */
    private FormFactoryInterface $formFactory;

    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    public function __construct(
        KernelInterface $kernel,
        EntityManagerInterface $em,
        FormFactoryInterface $formFactory,
        UserRepository $userRepository,
        UserPasswordHasherInterface $hasher,
    ) {
        $this->formFactory = $formFactory;
        $this->userRepository = $userRepository;
        $this->hasher = $hasher;

        parent::__construct($kernel, $em);
    }

    /**
     * @param JWTTokenManagerInterface $JWTManager
     * @param Request $request
     *
     * @return Response
     */
    #[Route(path: "/api/login", name: "api_login", methods: ["POST"])]
    public function index(JWTTokenManagerInterface $JWTManager, Request $request): Response
    {
        $payload = json_decode($request->getContent(), true);

        $form = $this->formFactory->create(LoginRequestType::class);

        if ($form->submit($payload) && !$form->isValid()) {
            return $this->json([
                'success' => false,
                'errors' => $form->getErrors(),
            ]);
        }

        $formData = $form->getData();
        /** @var User $user */
        $user = $this->userRepository->findOneBy(['email' => $formData['email']]);

        if (!$user) {
            return $this->json([
               'success' => false,
               'error' => 'No such user found',
            ]);
        }

        if (!$this->validatePassword($user, $formData['password'])) {
            return $this->json([
                'success' => false,
                'error' => 'incorrect password'
            ]);
        }

        if (!$user->getApiToken() || $user->getTokenExpireAt() < new DateTime()) {
            $user->setApiToken($JWTManager->create($user));
            $user->setTokenExpireAt((new DateTime())->modify('+1 hour'));

            $this->em->persist($user);
            $this->em->flush();
        }

        return $this->json([
            'success' => true,
            'user_id' => $user->getId(),
            'token' => $user->getApiToken(),
        ]);
    }

    /**
     * @param User $user
     * @param string $password
     *
     * @return bool
     */
    private function validatePassword(User $user, string $password): bool
    {
        return $this->hasher->isPasswordValid($user, $password);
    }
}
