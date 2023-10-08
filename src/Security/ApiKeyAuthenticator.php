<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;


class ApiKeyAuthenticator extends AbstractAuthenticator
{
    protected const HEADER_USER_ID    = 'id';
    protected const HEADER_AUTH_TOKEN = 'token';

    /**
     * @inheritDoc
     */
    public function supports(Request $request): ?bool
    {
         // get() сразу проверит наличие переданного user_id и сравнит его с 0
        return $request->headers->has(self::HEADER_AUTH_TOKEN)
            && $request->headers->get(self::HEADER_USER_ID);
    }

    /**
     * @inheritDoc
     */
    public function authenticate(Request $request): Passport
    {
        $userId   = $request->headers->get(self::HEADER_USER_ID);
        $apiToken = $request->headers->get(self::HEADER_AUTH_TOKEN);

        if (!$userId) {
            throw new CustomUserMessageAuthenticationException(
                'user_id is required and must be more than 0 (header: "{{ header }}")', [
                    '{{ header }}' => self::HEADER_USER_ID,
                ]
            );
        }

        if (null === $apiToken) {
            throw new CustomUserMessageAuthenticationException('Auth token not found (header: "{{ header }}")', [
                '{{ header }}' => self::HEADER_AUTH_TOKEN,
            ]);
        }

        return new SelfValidatingPassport(new UserBadge($apiToken));
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        throw $exception;
    }
}
