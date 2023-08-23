<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/profile', name: 'profile_')]
class ProfileController extends AbstractController
{

    #[Route(path: '{id}', name: 'index')]
    #[Route(path: '', name: 'index')]
    public function index(User $user): Response
    {
        return $this->render('profile/index.html.twig', [
            'user' => $user
        ]);
    }
}
