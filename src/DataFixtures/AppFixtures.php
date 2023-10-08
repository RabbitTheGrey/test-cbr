<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public const USER_EMAIL = 'user@example.com';
    public const USER_PASSWORD = 'securePassword';

    public function __construct(private readonly UserPasswordHasherInterface $hasher)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager): void
    {
        /**
         * Добавление пользователя
         */
        $user = new User();
        $pass = $this->hasher->hashPassword($user, self::USER_PASSWORD);
        $user
            ->setEmail(self::USER_EMAIL)
            ->setPassword($pass);

        $manager->persist($user);
        $manager->flush();
    }
}
