<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Votes;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/comment', name: 'comment_')]
class CommentController extends AbstractController
{
    #[Route('/{id}/up', name: 'up')]
    #[Route('/{id}/down', name: 'down')]
    public function up(Comment $comment, EntityManagerInterface $em, Request $request): Response
    {

        /**
         * @var User $user
         */
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour voter !');
            return $this->redirectToRoute('app_login');
        } elseif (
            $comment->getVotes()->exists(function ($key, $element) use ($user) {
                return $element->getUser() === $user;
            })
        ) {
            $this->addFlash('error', 'Vous avez déjà voté pour ce commentaire !');
        } else {
            $comment->setRating($comment->getRating() +  ($request->getPathInfo() === '/comment/' . $comment->getId() . '/up' ? 1 : -1));

            $vote = new Votes();
            $vote->setUser($this->getUser());
            $vote->setComment($comment);

            $em->persist($vote);
            $em->flush();

            $this->addFlash('success', 'Votre vote a bien été pris en compte !');
        }

        $referer = $request->headers->get('referer');
        return $referer ? $this->redirect($referer) : $this->redirectToRoute('home');
    }
}
