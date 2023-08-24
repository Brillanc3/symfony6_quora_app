<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/profile', name: 'profile_')]
class ProfileController extends AbstractController
{

    #[Route(path: '', name: 'index')]
    public function index()
    {
        $user = $this->getUser();

        // get linked acounts
        $linkedaccounts = $user->getLinkedAcounts();

        $linkableAccounts = ['google', 'discord', 'github', 'twitch', 'steam'];

        foreach ($linkedaccounts as $linkedaccount) {
            $key = array_search($linkedaccount->getType(), $linkableAccounts);
            if ($key !== false) unset($linkableAccounts[$key]);
        }

        if (!$user->getPassword()) $this->addFlash('error', 'Vous n\'avez pas de mot de passe, vous ne pourrez que vous connecter avec un compte lié !');

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'linkedaccounts' => $linkedaccounts,
            'linkableaccounts' => $linkableAccounts
        ]);
    }
}
