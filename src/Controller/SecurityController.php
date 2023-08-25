<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginFromType;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        $user = new User();
        $form = $this->createForm(LoginFromType::class, $user, [
            'action' => $this->generateUrl('app_login'),
            'method' => 'POST',
        ]);
        
        $form->handleRequest($request);

        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error'         => $authenticationUtils->getLastAuthenticationError(),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            // check if user have avatar
            if ($form->get('avatar')->getData()) {
                $file = $form->get('avatar')->getData();
                $fileName = uniqid() . '.' . $file->guessExtension();

                $file->move(
                    $this->getParameter('avatar_directory'),
                    $fileName
                );

                $user->setAvatar($fileName);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route(path: '/changepassword', name: 'changepassword')]
    public function customRegister(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $em): Response
    {

        /**
         * @var User $user
         */

        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('home');
        }

        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->remove('email');
        $form->remove('pseudonyme');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $em->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('security/changepassword.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/unlink/{type}', name: 'unlink')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function unlink(Request $request, EntityManagerInterface $em): Response
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();

        // get linked account from type
        $haveAccount = $user->getLinkedAcounts()->exists(function ($key, $element) use ($request) {
            return $element->getType() === $request->get('type');
        });

        if (!$haveAccount) return $this->render('security/unlink.html.twig', [
            'error' => 'Vous n\'avez pas de compte lié avec ' . $request->get('type') . ' !',
        ]);

        $confirmForm = $this->createFormBuilder()
            ->add('confirm', \Symfony\Component\Form\Extension\Core\Type\CheckboxType::class, [
                'label' => 'Je confirme vouloir supprimer mon compte lié',
                'required' => true,
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\IsTrue([
                        'message' => 'Vous devez confirmer vouloir supprimer votre compte lié',
                    ]),
                ],
            ])
            ->add('submit', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, [
                'label' => 'Supprimer mon compte lié',
                'attr' => [
                    'class' => 'btn btn-danger'
                ]
            ])
            ->getForm();

        return $this->render('security/unlink.html.twig', [
            'type' => $request->get('type'),
            'form' => $confirmForm->createView()
        ]);
    }

    #[Route(path: '/unlink/{type}/confirm', name: 'unlink_confirm')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function unlinkConfirm(): Response
    {
        $user = $this->getUser();

        dd($user);
        return $this->redirectToRoute('profile_index');
    }
}
