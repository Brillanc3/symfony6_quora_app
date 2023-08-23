<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Question;
use App\Form\CommentType;
use App\Form\QuestionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/question', name: 'question_')]
class QuestionController extends AbstractController
{

    #[Route('/ask', name: 'ask')]
    public function ask(Request $request, EntityManagerInterface $em)
    {

        $newQuestion = new Question();
        $questionForm = $this->createForm(QuestionType::class, $newQuestion);
        $questionForm->handleRequest($request);

        if ($questionForm->isSubmitted() && $questionForm->isValid()) {
            $newQuestion = $questionForm->getData();
            $newQuestion->setCreatedAt(new \DateTimeImmutable());
            $newQuestion->setNbResponse(0);
            $newQuestion->setRatting(0);

            $this->addFlash('success', 'Votre question a bien été ajoutée !');

            $em->persist($newQuestion);
            $em->flush();

            return $this->redirectToRoute('question_ask');
        }


        return $this->render('question/ask.html.twig', [
            'form' => $questionForm->createView(),
        ]);
    }


    #[Route('/{id}', name: 'show')]
    public function show(Question $question, Request $request, EntityManagerInterface $em)
    {
        $newComment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $newComment);
        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $newComment = $commentForm->getData();
            $newComment->setCreatedAt(new \DateTimeImmutable());
            $newComment->setQuestion($question);
            $newComment->setRating(0);

            $question->setNbResponse($question->getNbResponse() + 1);

            $this->addFlash('success', 'Votre commentaire a bien été ajouté !');

            $em->persist($newComment);
            $em->flush();
            $referer = $request->headers->get('HTTP_REFFERER');
            return $referer ? $this->redirect($referer) : $this->redirectToRoute('home');
        }

        return $this->render('question/show.html.twig', [
            'question' => $question,
            'form' => $commentForm->createView(),
        ]);
    }

    #[Route('/{id}/up', name: 'up')]
    #[Route('/{id}/down', name: 'down')]
    public function up(Question $question, EntityManagerInterface $em, Request $request)
    {
        $question->setRatting($question->getRatting() +  ($request->getPathInfo() === '/question/' . $question->getId() . '/up' ? 1 : -1));
        $em->flush();

        $referer = $request->headers->get('HTTP_REFFERER');
        return $referer ? $this->redirect($referer) : $this->redirectToRoute('home');
    }
}
