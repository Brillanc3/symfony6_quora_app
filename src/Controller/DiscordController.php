<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DiscordController extends AbstractController
{
    #[Route('/connect/discord', name: 'discord_connect')]
    public function connectAction(ClientRegistry $clientRegistry) : Response
    {
        return $clientRegistry->getClient('discord')->redirect([], []);
    }

    #[Route('/connect/discord/check', name: 'connect_discord_check')]
    public function check()
    {
        dd('test');
    }
}
