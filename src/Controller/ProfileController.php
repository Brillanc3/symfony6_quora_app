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
        /**
         * @var User $user
         */
        $user = $this->getUser();

        if(!$user) return $this->redirectToRoute('app_login');

        $linkedaccounts = $user->getLinkedAcounts();

        $linkableAccounts = ['google', 'discord', 'github', 'twitch', 'steam'];

        foreach ($linkedaccounts as $linkedaccount) {
            $key = array_search($linkedaccount->getType(), $linkableAccounts);
            if ($key !== false) unset($linkableAccounts[$key]);
        }

        $question = $user->getQuestions();
        $comment = $user->getComments();

        if (!$user->getPassword()) $this->addFlash('error', 'Vous n\'avez pas de mot de passe, vous ne pourrez que vous connecter avec un compte lié !<br> <a href="' . $this->generateUrl('app_register') . '">Créer un mot de passe</a>');

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'linkedaccounts' => $linkedaccounts,
            'linkableaccounts' => $linkableAccounts,
            'questions' => $question,
            'comments' => $comment,
        ]);
    }
}
