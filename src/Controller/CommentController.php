<?php

namespace App\Controller;

use App\Entity\Comment;
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
    public function up(Comment $comment, EntityManagerInterface $em, Request $request) : Response
    {
        $comment->setRating($comment->getRating() +  ($request->getPathInfo() === '/comment/' . $comment->getId() . '/up' ? 1 : -1));
        $em->flush();

        $referer = $request->headers->get('referer');
        return $referer ? $this->redirect($referer) : $this->redirectToRoute('home');
    }
}
