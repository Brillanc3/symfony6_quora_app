<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Component\HttpFoundation\Request;

class GoogleController extends AbstractController
{
    #[Route('/connect/google', name: 'google_connect')]
    public function connectAction(ClientRegistry $clientRegistry) : Response
    {
        return $clientRegistry->getClient('google')->redirect([], []);
    }

    #[Route('/connect/google/check', name: 'connect_google_check')]
    public function connectCheckAction()
    {}
}
