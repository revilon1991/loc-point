<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\LoginFormType;
use App\Form\Type\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;

class SecurityController extends AbstractController
{
    public function __construct(
        private UserPasswordEncoderInterface $passwordEncoder,
        private AuthenticationUtils $authenticationUtils,
        private LoginLinkHandlerInterface $loginLinkHandler,
    ) {
    }

    #[Route('/login', methods: ['GET'])]
    public function login(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_map_index');
        }

        $loginForm = $this->createForm(LoginFormType::class);

        $lastAuthenticationError = $this->authenticationUtils->getLastAuthenticationError();

        if ($lastAuthenticationError) {
            $message = $lastAuthenticationError->getMessage();

            $loginForm->addError(new FormError($message));
        }

        $loginForm->handleRequest($request);

        return $this->render('/login.html.twig', [
            'form' => $loginForm->createView(),
        ]);
    }

    #[Route('/login/check', methods: ['POST', 'GET'])]
    public function loginCheck(): void
    {
    }

    #[Route('/logout', methods: ['GET'])]
    public function logout(): void
    {
    }

    #[Route('/register')]
    public function register(
        Request $request
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $this->passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $loginLinkDetails = $this->loginLinkHandler->createLoginLink($user);
            $loginLink = $loginLinkDetails->getUrl();

            return $this->redirect($loginLink);
        }

        return $this->render('/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
