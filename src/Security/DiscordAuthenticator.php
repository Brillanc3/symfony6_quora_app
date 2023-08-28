<?php

namespace App\Security;

use App\Controller\SecurityController as SecurityController;
use App\Entity\LinkedAcount;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Grant\Password;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class DiscordAuthenticator extends OAuth2Authenticator
{
    private ClientRegistry $clientRegistry;
    private EntityManagerInterface $entityManager;
    private RouterInterface $router;

    public function __construct(
        ClientRegistry $clientRegistry,
        EntityManagerInterface $entityManager,
        RouterInterface $router
    ) {
        $this->clientRegistry = $clientRegistry;
        $this->entityManager = $entityManager;
        $this->router = $router;
    }

    public function supports(Request $request): ?bool
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return $request->attributes->get('_route') === 'connect_discord_check';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('discord');
        $accessToken = $this->fetchAccessToken($client);
        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client) {
                /**
                 * @var DiscordUser $discordUser
                 */
                $discordUser = $client->fetchUserFromToken($accessToken);
                $existingUser = $this->entityManager->getRepository(LinkedAcount::class)->findOneBy(['typeId' => $discordUser->getId()]);

                if($existingUser) {
                    return $existingUser->getUser();
                } else {
                    $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $discordUser->getEmail()]);

                    if($existingUser) {
                        $linkedAcount = new LinkedAcount();
                        $linkedAcount->setType('discord');
                        $linkedAcount->setTypeId($discordUser->getId());
                        $linkedAcount->setUser($existingUser);
                        $linkedAcount->setUsername($discordUser->getUsername());

                        $existingUser->addLinkedAcount($linkedAcount);
                        $this->entityManager->persist($linkedAcount);
                        $this->entityManager->flush();

                        return $existingUser;
                    }
                }

                $u = new User();
                $u->setEmail($discordUser->getEmail());
                $u->setPseudonyme($discordUser->getUsername());
                $u->setAvatar('https://cdn.discordapp.com/avatars/' . $discordUser->getId() . '/' . $discordUser->getAvatarHash() . '.png');
                $u->setPassword('');

                $linkedAcount = new LinkedAcount();
                $linkedAcount->setType('discord');
                $linkedAcount->setTypeId($discordUser->getId());
                $linkedAcount->setUser($u);

                $u->addLinkedAcount($linkedAcount);

                $this->entityManager->persist($u);
                $this->entityManager->flush();

                return $u;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse(
            // $secu->linkaccount($user)
            $this->router->generate('linkaccount')
        );
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    //    public function start(Request $request, AuthenticationException $authException = null): Response
    //    {
    //        /*
    //         * If you would like this class to control what happens when an anonymous user accesses a
    //         * protected page (e.g. redirect to /login), uncomment this method and make this class
    //         * implement Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface.
    //         *
    //         * For more details, see https://symfony.com/doc/current/security/experimental_authenticators.html#configuring-the-authentication-entry-point
    //         */
    //    }
}
