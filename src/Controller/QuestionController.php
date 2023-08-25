<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Question;
use App\Entity\User;
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

        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour poser une question !');
            return $this->redirectToRoute('app_login');
        }
        $newQuestion = new Question();
        $questionForm = $this->createForm(QuestionType::class, $newQuestion);
        $questionForm->handleRequest($request);

        if ($questionForm->isSubmitted() && $questionForm->isValid()) {
            $newQuestion = $questionForm->getData();
            $newQuestion->setCreatedAt(new \DateTimeImmutable());
            $newQuestion->setNbResponse(0);
            $newQuestion->setRatting(0);
            $newQuestion->setUser($user);

            $this->addFlash('success', 'Votre question a bien été ajoutée !');

            $em->persist($newQuestion);
            $em->flush();

            return $this->redirectToRoute('question_ask');
        }


        return $this->render('question/ask.html.twig', [
            'form' => $questionForm->createView(),
        ]);
    }
    
    #[Route('/me', name: 'me')]
    public function showMe()
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page !');
            return $this->redirectToRoute('app_login');
        }

        $questions = $user->getQuestions();

        return $this->render('home/index.html.twig', [
            'questions' => $questions,
        ]);
    }

    #[Route('/{id}', name: 'show')]
    public function show(Question $question, Request $request, EntityManagerInterface $em)
    {

        if($this->getUser()) {
            $newComment = new Comment();
            $commentForm = $this->createForm(CommentType::class, $newComment);
            $commentForm->handleRequest($request);
    
            $options['form'] = $commentForm->createView();

            if ($commentForm->isSubmitted() && $commentForm->isValid()) {
                $newComment = $commentForm->getData();
                $newComment->setCreatedAt(new \DateTimeImmutable());
                $newComment->setQuestion($question);
                $newComment->setRating(0);
                $newComment->setUser($this->getUser());
    
                $question->setNbResponse($question->getNbResponse() + 1);
    
                $this->addFlash('success', 'Votre commentaire a bien été ajouté !');
                $em->persist($newComment);
                
                $em->flush();
                $referer = $request->headers->get('referer');
                return $referer ? $this->redirect($referer) : $this->redirectToRoute('home');
            }
        }

        $options['question'] = $question;


        return $this->render('question/show.html.twig', $options);
    }

    #[Route('/{id}/up', name: 'up')]
    #[Route('/{id}/down', name: 'down')]
    public function up(Question $question, EntityManagerInterface $em, Request $request) : Response
    {
        if ($this->getUser()) {
            $question->setRatting($question->getRatting() +  ($request->getPathInfo() === '/question/' . $question->getId() . '/up' ? 1 : -1));
            $em->flush();
        } else {
            $this->addFlash('error', 'Vous devez être connecté pour voter !');
        }
        $referer = $request->headers->get('referer');
        return $referer ? $this->redirect($referer) : $this->redirectToRoute('home');
    }

}
