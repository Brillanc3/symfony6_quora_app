<?php
# src/Security/GoogleAuthenticator.php
namespace App\Security;

use App\Entity\LinkedAcount;
use App\Entity\User;
use League\OAuth2\Client\Provider\GoogleUser;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class GoogleAuthenticator extends OAuth2Authenticator
{
    private ClientRegistry $clientRegistry;
    private EntityManagerInterface $entityManager;
    private RouterInterface $router;

    
    public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $entityManager, RouterInterface $router)
    {
        $this->clientRegistry = $clientRegistry;
        $this->entityManager = $entityManager;
        $this->router = $router;
    }

    public function supports(Request $request): ?bool
    {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('google');
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client) {
                /** @var GoogleUser $googleUser */
                $googleUser = $client->fetchUserFromToken($accessToken);
                $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $googleUser->getEmail()]);
                //User doesnt exist, we create it !

                if (!$existingUser) {
                    $existingUser = new User();
                    $existingUser->setEmail( $googleUser->getEmail());
                    $existingUser->setPseudonyme($googleUser->getName());
                    $existingUser->setAvatar($googleUser->getAvatar());
                    $existingUser->setPassword('');

                    $linkedAcount = new LinkedAcount();
                    $linkedAcount->setType('google');
                    $linkedAcount->setTypeId((int) $googleUser->getId());
                    $linkedAcount->setUser($existingUser);
                    $linkedAcount->setUsername($googleUser->getName());

                    $existingUser->addLinkedAcount($linkedAcount);
                    $this->entityManager->persist($existingUser);
                    $this->entityManager->persist($linkedAcount);
                    $this->entityManager->flush();
                } else {
                    // check if there is already an account with this discord id
                    $linkedUser = $this->entityManager->getRepository(LinkedAcount::class)->findOneBy([ 'user' => $existingUser->getId() , 'type' => 'google']);

                    if ($linkedUser && $linkedUser->getUser()) {
                        $existingUser = $linkedUser->getUser();
                        return $existingUser;
                    }

                    $linkedAcount = new LinkedAcount();
                    $linkedAcount->setType('google');
                    $linkedAcount->setTypeId((int) $googleUser->getId());
                    $linkedAcount->setUser($existingUser);
                    $linkedAcount->setUsername($googleUser->getName());


                    $existingUser->addLinkedAcount($linkedAcount);
                    $existingUser->setAvatar($googleUser->getAvatar());

                    $this->entityManager->persist($linkedAcount);
                    $this->entityManager->flush();
                }

                return $existingUser;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {

        // change "app_dashboard" to some route in your app
        return new RedirectResponse(
            $this->router->generate('home')
        );

        // or, on success, let the request continue to be handled by the controller
        //return null;
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